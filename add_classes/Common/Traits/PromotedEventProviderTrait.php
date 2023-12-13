<?php

declare(strict_types=1);

namespace App\Common\Traits;

use DateTimeImmutable;

trait PromotedEventProviderTrait
{
    /**
     * Returns information (if any) for promoted events prepared to be displayed in the template.
     */
    private function getPromotedEventDisplayInformation(): ?array
    {
        /** @var \Ep_Events_Model $eventsRepository */
        $eventsRepository = model(\Ep_Events_Model::class);
        $promotedEvent = $eventsRepository->findCurrentPromotedEvent();
        if (!$promotedEvent) {
            return null;
        }

        $currentDate = new DateTimeImmutable();
        $eventPromotion = [
            'id' => $promotedEvent['id'],
        ];
        $dateDiff = $promotedEvent['start_date']->diff($currentDate);
        $eventPromotion['url'] = getEpEventDetailUrl($promotedEvent);
        $eventPromotion['title'] = $promotedEvent['title'];
        $eventPromotion['countdown'] = [
            'text'     => getTimeInterval($promotedEvent['start_date']->format(DATE_ATOM), $promotedEvent['end_date']->format(DATE_ATOM), DATE_ATOM),
            'days'     => str_pad($dateDiff->format('%a'), 2, '0', \STR_PAD_LEFT),
            'hours'    => $dateDiff->format('%H'),
            'minutes'  => $dateDiff->format('%I'),
            'seconds'  => $dateDiff->format('%S'),
            'end_date' => $promotedEvent['start_date'],
        ];
        $eventPromotion['images'] = array_filter([
            'original' => getDisplayImageLink(['{ID}' => $promotedEvent['id'], '{FILE_NAME}' => $promotedEvent['main_image']], 'ep_events.main'),
            'desktop'  => getDisplayImageLink(['{ID}' => $promotedEvent['id'], '{FILE_NAME}' => $promotedEvent['main_image']], 'ep_events.main', ['thumb_size' => 0]),
            'tablet'   => getDisplayImageLink(['{ID}' => $promotedEvent['id'], '{FILE_NAME}' => $promotedEvent['main_image']], 'ep_events.main', ['thumb_size' => 1]),
        ]);

        return $eventPromotion;
    }
}
