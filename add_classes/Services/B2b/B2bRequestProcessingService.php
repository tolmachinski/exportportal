<?php

declare(strict_types=1);

namespace App\Services\B2b;
use App\DataProvider\B2bRequestProvider;
use App\Common\Database\Exceptions\WriteException;
use App\Common\Database\Model;
use App\Common\Contracts\B2B\B2bRequestLocationType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\MessageBusInterface;
use TinyMVC_Library_Cleanhtml as LegacyHtmlSanitizer;
use User_Statistic_Model;

/**
 * @author Bendiucov Tatiana
 */
final class B2bRequestProcessingService
{
    /**
     * The b2b data provider.
     */
    private B2bRequestProvider $b2bRequestProvider;
    /**
     * The repository for b2b.
     */
    private Model $b2bRepository;

    /**
     * The repository for b2b.
     */
    private User_Statistic_Model $userStatisticsRepository;

    /**
     * Create the service.
     */
    public function __construct(
        B2bRequestProvider $b2bRequestProvider,
        User_Statistic_Model $userStatisticsRepository
    ) {
        $this->b2bRequestProvider = $b2bRequestProvider;
        $this->b2bRepository = $b2bRequestProvider->getRepository();
        $this->userStatisticsRepository = $userStatisticsRepository;
    }

    public function saveB2bRequest(Request $request, int $userId)
    {
        $b2bData = $this->getFinalDataForDatabase($userId, $request);
        $b2bData['id_request'] = $b2bId = (int) $this->b2bRepository->insertOne($b2bData);

        //insert industries, categories and countries in the pivot tables
        $this->updateB2bIndustries($b2bId, (array) $request->request->get('industriesSelected'));
        $this->updateB2bCategories($b2bId, (array) $request->request->get('categoriesSelected'));
        $this->updateB2bCountries($b2bId, (array) $request->request->get('countries'));

        //update statistics for user
        $this->userStatisticsRepository->set_users_statistic([
            $userId => ['b2b_requests' => 1],
        ]);

        return $b2bData;
    }

    public function updateB2bRequest(int $requestId, Request $request, int $userId)
    {
        $b2bData = $this->getFinalDataForDatabase($userId, $request);
        //if in the request before editing the type location was by country
        //and now it is by radius or globally,
        ///we must delete the existing countries from database
        $existingB2b = $this->b2bRequestProvider->getRequest($requestId);

        if(
            B2bRequestLocationType::COUNTRY() === $existingB2b['type_location']
            && B2bRequestLocationType::COUNTRY() !== $b2bData['type_location']
        ){
            $this->deleteB2bCountries($requestId);
        }
        $this->b2bRepository->updateOne($requestId, $b2bData);

        //insert industries, categories and countries in the pivot tables
        $this->updateB2bIndustries($requestId, (array) $request->request->get('industriesSelected'));
        $this->updateB2bCategories($requestId, (array) $request->request->get('categoriesSelected'));
        if($b2bData['type_location'] == B2bRequestLocationType::COUNTRY()){
            $this->updateB2bCountries($requestId, (array) $request->request->get('countries'));
        }

        $b2bData['id_request'] = $requestId;
        return $b2bData;
    }

    /**
     * Get the data in the right format for inserting/updating
     *
     * @param int $userId
     * @param Request $request
     *
     * @return array
     */
    private function getFinalDataForDatabase(int $userId, Request $request)
    {
        //sanitize the message with our html sanitizer
        $sanitizer = \library(LegacyHtmlSanitizer::class);
        $sanitizer->allowIframes();
        $sanitizer->defaultTextarea();
        $sanitizer->addAdditionalTags('<img><h3><h4><h5><h6><p><span><strong><em><b><i><u><a><ol><ul><li><br>');
        $sanitizedMessage = $sanitizer->sanitize($request->request->get('message'));

        $radius = $request->request->getInt('radius') ?? 0;
        if(B2bRequestLocationType::RADIUS() !== B2bRequestLocationType::tryFrom($request->request->get('type_location'))){
            $radius = 0;
        }

        return [
            'id_company'     => $request->request->getInt('company_branch'),
            'id_user'        => $userId,
            'id_type'        => $request->request->getInt('p_type'),
            'type_location'  => B2bRequestLocationType::tryFrom($request->request->get('type_location')),
            'b2b_radius'     => $radius,
            'b2b_title'      => cleanInput($request->request->get('title')),
            'b2b_message'    => $sanitizedMessage,
            'b2b_tags'       => $request->request->get('tags'),
        ];
    }

    /**
     * Updates the b2b industries.
     *
     * @throws \RuntimeException if b2b pivot is invalid
     * @throws WriteException    when failed to write the industries
     */
    private function updateB2bIndustries(int $b2bId, array $industries): void
    {
        // If we have no industries - we must leave.
        if (empty($industries) || empty($b2bId)) {
            return;
        }

        // We cannot allow here any errors, so if by ANY slight chance that the pivot model will be
        // the b2b repository, we will throw exception to stop operation.
        $pivot = $this->b2bRepository->getRelation('industries')->getParent();
        if (\get_class($this->b2bRepository) === \get_class($pivot)) {
            throw new \RuntimeException(\sprintf('The pivot cannot be instance of "%s" class', \get_class($this->b2bRepository)));
        }

        // Otherwise we happily delete old industries and insert new industries.
        if (
            !$pivot->deleteAllBy(['scopes' => ['id_request' => $b2bId]])
            || !$pivot->insertMany(
                \array_map(
                    fn (int $id) => ['id_request' => $b2bId, 'id_industry' => $id],
                    $industries
                )
            )
        ) {
            throw new WriteException(\sprintf('Failed to update industries for request with ID "%s".', $b2bId));
        }
    }

    /**
     * Updates the b2b categories.
     *
     * @throws \RuntimeException if b2b pivot is invalid
     * @throws WriteException    when failed to write the categories
     */
    private function updateB2bCategories(int $b2bId, array $categories): void
    {
        // If we have no categories - we must leave.
        if (empty($categories) || empty($b2bId)) {
            return;
        }

        // We cannot allow here any errors, so if by ANY slight chance that the pivot model will be
        // the b2b repository, we will throw exception to stop operation.
        $pivot = $this->b2bRepository->getRelation('categories')->getParent();
        if (\get_class($this->b2bRepository) === \get_class($pivot)) {
            throw new \RuntimeException(\sprintf('The pivot cannot be instance of "%s" class', \get_class($this->b2bRepository)));
        }

        // Otherwise we happily delete the old request categories and insert new categories.
        if (
            !$pivot->deleteAllBy(['scopes' => ['id_request' => $b2bId]])
            || !$pivot->insertMany(
                \array_map(
                    fn (int $id) => ['id_request' => $b2bId, 'id_category' => $id],
                    $categories
                )
            )
        ) {
            throw new WriteException(\sprintf('Failed to update categories for request with ID "%s".', $b2bId));
        }
    }

    /**
     * Updates the b2b countries.
     *
     * @throws \RuntimeException if b2b pivot is invalid
     * @throws WriteException    when failed to write the countries
     */
    private function updateB2bCountries(int $b2bId, array $countries): void
    {
        // If we have no countries - we must leave.
        if (empty($countries) || empty($b2bId)) {
            return;
        }

        // We cannot allow here any errors, so if by ANY slight chance that the pivot model will be
        // the b2b repository, we will throw exception to stop operation.
        $pivot = $this->b2bRepository->getRelation('countries')->getParent();
        if (\get_class($this->b2bRepository) === \get_class($pivot)) {
            throw new \RuntimeException(\sprintf('The pivot cannot be instance of "%s" class', \get_class($this->b2bRepository)));
        }

        // Otherwise we happily delete the old request countries and insert new countries.
        if (
            !$pivot->deleteAllBy(['scopes' => ['id_request' => $b2bId]])
            || !$pivot->insertMany(
                \array_map(
                    fn (int $id) => ['request_id' => $b2bId, 'country_id' => $id],
                    $countries
                )
            )
        ) {
            throw new WriteException(\sprintf('Failed to update countries for request with ID "%s".', $b2bId));
        }
    }

    private function deleteB2bCountries(int $b2bId)
    {
        // If we have no id - we must leave.
        if (empty($b2bId)) {
            return;
        }

        // We cannot allow here any errors, so if by ANY slight chance that the pivot model will be
        // the b2b repository, we will throw exception to stop operation.
        $pivot = $this->b2bRepository->getRelation('countries')->getParent();
        if (\get_class($this->b2bRepository) === \get_class($pivot)) {
            throw new \RuntimeException(\sprintf('The pivot cannot be instance of "%s" class', \get_class($this->b2bRepository)));
        }

        $pivot->deleteAllBy(['scopes' => ['id_request' => $b2bId]]);
    }

}
