<?php

use App\Filesystem\EpNewsImagePathGenerator;
use App\Filesystem\MassMediaFilesPathGenerator;
use App\Filesystem\OurTeamFilesPathGenerator;
use App\Filesystem\PartnersUploadsPathGenerator;
use App\Filesystem\VacancyPathGenerator;
use ExportPortal\Contracts\Filesystem\FilesystemOperator;
use ExportPortal\Contracts\Filesystem\FilesystemProviderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class About_Controller extends TinyMVC_Controller
{
	private $breadcrumbs = array();
	private $vIcon = '<i class="ep-icon ep-icon_ok txt-blue2"></i>';
	private $xIcon = '<i class="ep-icon ep-icon_remove-stroke2 txt-gray-light"></i>';

    private FilesystemOperator $storage;
    private FilesystemOperator $tempStorage;

     /**
     * Controller constructor.
     */
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);

        /** @var FilesystemProviderInterface */
        $storageProvider = $container->get(FilesystemProviderInterface::class);

        $this->storage = $storageProvider->storage('public.storage');
        $this->tempStorage = $storageProvider->storage('temp.storage');
    }


    private function generateButton($type)
    {
        return '<a href="'. __SITE_URL .'register/' . $type . '" class="btn btn-primary btn-block">' . translate('about_us_certification_and_upgrade_benefits_register_btn') . '</a>';
    }

    private function icons()
    {
        return [
            'globe'                  => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="translate(-25.975,-25.969)"><g transform="matrix(1.6663 0 0 1.6663 25.985 25.984)" fill="#2181f8"><path transform="translate(-78.168,-78.171)" d="m108.17 130.34a22.186 22.186 0 1 0-19.152-11.013 1.3 1.3 0 0 0 0.4 0.65 22.174 22.174 0 0 0 18.752 10.363zm-16.945-31.941h7.3a40.415 40.415 0 0 0-1.159 8.462h-8.714a19.424 19.424 0 0 1 2.573-8.462zm36.463 8.462h-8.714a40.408 40.408 0 0 0-1.159-8.462h7.3a19.419 19.419 0 0 1 2.572 8.467zm-2.44 10.834h-7.388a39.972 39.972 0 0 0 1.113-8.226h8.714a19.418 19.418 0 0 1-2.439 8.23zm-18.382 2.608v6.569c-2.31-1-3.972-4.217-4.86-6.569zm-5.7-2.608a36.857 36.857 0 0 1-1.194-8.226h6.89v8.23zm8.3 9.177v-6.569h4.86c-0.881 2.358-2.542 5.576-4.852 6.574zm0-9.177v-8.226h6.89a36.857 36.857 0 0 1-1.194 8.226zm6.89-10.834h-6.89v-8.462h5.646a37.281 37.281 0 0 1 1.252 8.467zm-6.89-11.07v-6.512c2.269 1 3.912 4.189 4.8 6.516zm-2.608-6.516v6.524h-4.8c0.895-2.33 2.538-5.517 4.807-6.52zm0 9.124v8.462h-6.89a37.273 37.273 0 0 1 1.244-8.462zm-9.5 11.07a39.981 39.981 0 0 0 1.113 8.226h-7.38a19.421 19.421 0 0 1-2.44-8.226zm-4.526 10.834h6.4a19.943 19.943 0 0 0 3.6 6.684 19.618 19.618 0 0 1-9.992-6.679zm20.662 6.684a19.948 19.948 0 0 0 3.6-6.684h6.4a19.619 19.619 0 0 1-9.992 6.69zm9.806-31.2h-6.263a20.561 20.561 0 0 0-3.375-6.4 19.615 19.615 0 0 1 9.647 6.412zm-20.64-6.4a20.558 20.558 0 0 0-3.375 6.4h-6.254a19.615 19.615 0 0 1 9.638-6.388z"/><path transform="translate(-25.975,-25.969)" d="m35.687 71.263a1.3 1.3 0 0 0-1.3 1.3v0.254a27.4 27.4 0 0 1 33.02-41.747 1.3037 1.3037 0 1 0 1.087-2.37 30 30 0 0 0-35.748 46.252h-0.746a1.304 1.304 0 1 0 0 2.608c3.948 0 3.86 0.036 4.188-0.1a1.306 1.306 0 0 0 0.706-0.705c0.134-0.325 0.1-0.238 0.1-4.188a1.3 1.3 0 0 0-1.307-1.304z"/><path transform="translate(-139.04 -78.171)" d="m191.78 88.609h0.51a1.304 1.304 0 1 0 0-2.608h-3.689a1.3 1.3 0 0 0-1.3 1.3v3.689a1.3045 1.3045 0 0 0 2.609 0v-0.561a27.394 27.394 0 0 1-32.075 42.738 1.3039 1.3039 0 1 0-1.066 2.38 30 30 0 0 0 35.016-46.941z"/></g></g></svg>',
            'protection'             => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1.6393 0 0 1.6393 .81967 .81967)" fill="#2181f8" stroke="#fff"><path d="m58.243 19.28h-5.274v-1.992a4.108 4.108 0 0 0-2.54-3.792v-7.05a6.452 6.452 0 0 0-6.445-6.446h-2.456a6.452 6.452 0 0 0-6.445 6.445v7.055a4.107 4.107 0 0 0-2.539 3.792v1.988h-3.094l-4.218-5.208a1.758 1.758 0 0 0-1.366-0.651h-22.108a1.758 1.758 0 0 0-1.758 1.757v43.064a1.758 1.758 0 0 0 1.758 1.758h56.485a1.758 1.758 0 0 0 1.757-1.758v-37.2a1.758 1.758 0 0 0-1.758-1.758zm-22.184-1.992a0.586 0.586 0 0 1 0.586-0.586h12.222a0.587 0.587 0 0 1 0.586 0.586v13.465a0.587 0.587 0 0 1-0.586 0.586h-12.222a0.587 0.587 0 0 1-0.586-0.586zm2.541-10.842a2.933 2.933 0 0 1 2.93-2.93h2.456a2.933 2.933 0 0 1 2.93 2.93v6.741h-8.316zm17.885 50.038h-52.969v-39.548h19.511l4.218 5.208a1.757 1.757 0 0 0 1.366 0.652h3.932v7.957a4.106 4.106 0 0 0 4.1 4.1h12.224a4.106 4.106 0 0 0 4.1-4.1v-7.953h3.516z"/><path transform="translate(-299.14 -149.82)" d="m340.02 174.98v1.354a1.758 1.758 0 1 0 3.516 0v-1.354a2.93 2.93 0 1 0-3.516 0z"/><path transform="translate(-58.265 -316.65)" d="m67.758 362.2h12.07a1.758 1.758 0 1 0 0-3.516h-12.07a1.758 1.758 0 1 0 0 3.516z"/><path transform="translate(-58.265 -369.62)" d="m86.859 418.69h-19.1a1.758 1.758 0 1 0 0 3.516h19.1a1.758 1.758 0 1 0 0-3.516z"/></g></svg>',
            'certificate'            => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="m97.914-4.2212e-7h-95.827a2.0832 2.0832 0 0 0-2.0832 2.0832v74.995a2.0832 2.0832 0 0 0 2.0832 2.0832h56.247v18.749a2.0832 2.0832 0 0 0 3.4165 1.6049l11.166-9.3128 11.166 9.3128a2.0815 2.0815 0 0 0 3.4165-1.6049v-18.749h10.416a2.0832 2.0832 0 0 0 2.0832-2.0832v-74.995a2.0832 2.0832 0 0 0-2.0832-2.0832zm-14.582 42.394a20.832 20.832 0 0 0-31.248 18.019 13.821 13.821 0 0 0 0.10499 2.0832h-35.519v-45.831h66.663zm-27.082 18.019a16.666 16.666 0 1 1 16.666 16.666 16.666 16.666 0 0 1-16.666-16.666zm17.999 25.477a2.0832 2.0832 0 0 0-2.6665 0l-9.0828 7.5829v-15.039a20.685 20.685 0 0 0 20.832 0v15.041zm21.582-10.894h-8.0612a20.832 20.832 0 0 0-0.27165-29.435v-30.978a2.0832 2.0832 0 0 0-2.0832-2.0832h-70.829a2.0832 2.0832 0 0 0-2.0832 2.0832v49.997a2.0832 2.0832 0 0 0 2.0832 2.0832h38.456a20.832 20.832 0 0 0 5.0214 8.3328h-53.893v-70.829h91.661zm-72.912-47.914a2.0832 2.0832 0 0 1 2.0832-2.0832h24.998a2.0832 2.0832 0 0 1 0 4.1664h-24.998a2.0832 2.0832 0 0 1-2.0832-2.0832zm0 12.499a2.0832 2.0832 0 0 1 2.0832-2.0832h18.749a2.0832 2.0832 0 0 1 0 4.1664h-18.749a2.0832 2.0832 0 0 1-2.0832-2.0832z" fill="#2181f8" stroke-width="1.6666"/></svg>',
            'support'                => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><path d="m89.516 34.086h-1.6475c-4.5656-19.443-20.098-33.262-37.879-33.262-17.682-0.0032787-33.2 13.82-37.841 33.262h-1.6672c-5.3279 0-9.6623 4.9787-9.6623 11.097v12.459c0 6.118 4.3344 11.097 9.6623 11.097h1.2016c1.5459 13.975 11.967 24.816 24.548 24.816h2.3066a8.1082 8.1082 0 0 0 7.3918 5.6213h8.1393c4.4262 0 8.0328-4.1377 8.0328-9.223 0-5.0852-3.6066-9.2246-8.0328-9.2246h-8.1377a8.1082 8.1082 0 0 0-7.3902 5.6197h-2.3115c-10.185 0-18.47-9.5082-18.47-21.213v-27.049c3.2475-17.433 16.749-30.066 32.225-30.066 15.574 0 29.095 12.623 32.256 30.052v27.049a3.3951 3.3951 0 0 0 3.1377 3.6066h4.141c5.3279 0.01311 9.6623-4.9705 9.6623-11.084v-12.459c0-6.1246-4.3344-11.1-9.6639-11.1zm-43.585 53.854h8.1393a2.0361 2.0361 0 0 1 0 4.0328h-8.1393a2.0361 2.0361 0 0 1 0-4.0328zm-38.836-30.292v-12.459a3.6689 3.6689 0 0 1 3.3836-3.8902h1.0066v20.238h-1.0033a3.6672 3.6672 0 0 1-3.3869-3.8885zm85.811 0a3.6689 3.6689 0 0 1-3.3869 3.8902h-1.0033v-20.249h1.0033a3.6689 3.6689 0 0 1 3.3869 3.8902z" fill="#2181f8" stroke="#fff"/></svg>',
            'security'               => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="translate(0 .644)"><path d="m48.831 98.346c-42.503-14.284-41.839-48.131-41.352-72.845 0.05246-2.6558 0.10328-5.1657 0.10328-7.5936a3.4985 3.4985 0 0 1 3.5526-3.4427c15.186 0 26.747-4.2329 36.385-13.318a3.6329 3.6329 0 0 1 4.951 0c9.638 9.0855 21.202 13.318 36.394 13.318a3.4985 3.4985 0 0 1 3.5526 3.4427c0 2.4279 0.04918 4.9362 0.10328 7.592 0.49182 24.717 1.1476 58.562-41.355 72.848a3.6558 3.6558 0 0 1-2.3247 0zm-34.171-77.066c-0.01967 1.4148-0.04754 2.8607-0.07869 4.3509-0.49182 24.845-1.0394 52.969 35.411 65.807 36.455-12.84 35.903-40.965 35.411-65.81-0.02787-1.4902-0.05902-2.9345-0.07869-4.3493-14.223-0.58034-25.597-4.7542-35.337-12.984-9.7314 8.2248-21.102 12.402-35.327 12.986zm27.911 40.032-9.8248-9.5248a3.3673 3.3673 0 0 1 0-4.869 3.6263 3.6263 0 0 1 5.0214 0l7.3133 7.092 17.138-16.617a3.628 3.628 0 0 1 5.0214 0 3.364 3.364 0 0 1 0 4.8673l-19.65 19.051a3.628 3.628 0 0 1-5.0214 0z" fill="#2181f8" stroke="#fff"/></g></svg>',
            'professional_expertise' => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1.639 0 0 1.639 .82882 .8191)" fill="#2181f8"><path d="m59.989 14.8a1.875 1.875 0 0 0-1.071-1.5l-28.125-13.124a1.876 1.876 0 0 0-1.586 0l-28.125 13.124a1.875 1.875 0 0 0-1.07 1.5c0 0.013-0.012 0.193-0.012 0.2v31.876a1.875 1.875 0 0 0 1.179 1.741l28.121 11.249a1.874 1.874 0 0 0 1.393 0l7.621-3.048a13.118 13.118 0 0 0 21.686-9.942v-31.876c0-9e-3 -0.01-0.189-0.011-0.2zm-29.989-10.855 23.407 10.923-9.013 3.605-23.848-10.117zm-1.875 51.411-24.375-9.75v-27.836l24.375 9.75zm1.875-31.125-23.408-9.363 9.381-4.378 23.478 9.96zm16.875 32.02a9.375 9.375 0 1 1 9.375-9.375 9.386 9.386 0 0 1-9.375 9.375zm0-22.5a13.119 13.119 0 0 0-11.138 20.061l-3.862 1.545v-27.837l24.375-9.75v19.93a13.085 13.085 0 0 0-9.375-3.949z" stroke="#fff"/><path transform="translate(-27.259,-35.76)" d="m78.051 79.236-5.359 5.764-2.292-2.459a1.056 1.056 0 0 0-1.57 0 1.26 1.26 0 0 0 0 1.69l3.072 3.305a1.056 1.056 0 0 0 1.57 0l6.144-6.611a1.26 1.26 0 0 0 0-1.689 1.056 1.056 0 0 0-1.565 0z"/></g></svg>',
            'calendar'               => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1.6444 0 0 1.6444 -.15479 .67706)"><g transform="translate(3)" stroke="#fff" stroke-width=".8"><path transform="translate(-1.672)" d="m54.03 7.369h-8.4c-0.17-2.939-0.842-5.026-2-6.2a3.964 3.964 0 0 0-3.568-1.11c-3.931 0.613-4.092 5.619-4.032 7.31h-16.794c-0.173-2.919-0.844-4.989-1.994-6.158a3.991 3.991 0 0 0-3.569-1.111c-3.9 0.607-4.087 5.587-4.034 7.269h-5.327a2.642 2.642 0 0 0-2.64 2.636v47.36a2.642 2.642 0 0 0 2.64 2.635h32.174a4.4 4.4 0 0 0 2.766-1.117 2.469 2.469 0 0 0 0.514-0.516l15.787-20.89a6.111 6.111 0 0 0 1.119-3.33v-24.141a2.64 2.64 0 0 0-2.642-2.637zm-13.545-4.635a1.268 1.268 0 0 1 1.217 0.338c0.667 0.678 1.088 2.16 1.214 4.3h-4.173c-0.068-1.38 0.123-4.386 1.742-4.638zm-26.39 0.042a1.26 1.26 0 0 1 1.216 0.338c0.667 0.674 1.085 2.14 1.214 4.256h-4.171c-0.03-1.06 0.018-4.328 1.74-4.594zm25.031 52.524v-18.241a0.278 0.278 0 0 1 0.28-0.279h13.714zm15.184-21.153a1.62 1.62 0 0 1-0.03 0.291 2.266 2.266 0 0 0-0.249-0.015h-14.631a2.64 2.64 0 0 0-2.642 2.636v20.3c0 0.079 0 0.156 9e-3 0.229a1.059 1.059 0 0 1-0.286 0.046h-32.172a0.277 0.277 0 0 1-0.278-0.279v-37.119h50.279zm0-16.619h-50.276v-7.522a0.277 0.277 0 0 1 0.278-0.278h12.243c-0.017 0.595-0.049 1.208-0.1 1.83a2.753 2.753 0 0 0-1.854 2.588 2.8025 2.8025 0 0 0 5.605 0 2.7 2.7 0 0 0-1.059-2.139c0.065-0.777 0.108-1.541 0.125-2.28h23.671c-0.018 0.671-0.056 1.368-0.116 2.073a2.766 2.766 0 0 0-1.316 2.345 2.8 2.8 0 1 0 4.055-2.479c0.05-0.651 0.085-1.3 0.1-1.94h8.369a0.278 0.278 0 0 1 0.28 0.278v7.522z"/><path transform="translate(-.019 8.392)" d="m8.012 23.9h1.388a3.165 3.165 0 0 0 3.163-3.159v-1.378a3.164 3.164 0 0 0-3.163-3.157h-1.388a3.164 3.164 0 0 0-3.161 3.157v1.381a3.164 3.164 0 0 0 3.161 3.156zm-0.451-4.54a0.452 0.452 0 0 1 0.451-0.451h1.388a0.452 0.452 0 0 1 0.451 0.451v1.381a0.453 0.453 0 0 1-0.451 0.459h-1.388a0.452 0.452 0 0 1-0.451-0.452z"/><path transform="translate(3.246,8.392)" d="m14.295 23.9h1.383a3.165 3.165 0 0 0 3.163-3.159v-1.378a3.165 3.165 0 0 0-3.163-3.157h-1.383a3.165 3.165 0 0 0-3.163 3.157v1.381a3.165 3.165 0 0 0 3.163 3.156zm-0.453-4.54a0.453 0.453 0 0 1 0.451-0.451h1.383a0.452 0.452 0 0 1 0.451 0.451v1.381a0.453 0.453 0 0 1-0.451 0.452h-1.383a0.452 0.452 0 0 1-0.451-0.452z"/><path transform="translate(6.512,8.392)" d="m20.577 23.9h1.383a3.165 3.165 0 0 0 3.161-3.159v-1.378a3.164 3.164 0 0 0-3.161-3.157h-1.383a3.166 3.166 0 0 0-3.164 3.157v1.381a3.168 3.168 0 0 0 3.164 3.156zm-0.453-4.54a0.453 0.453 0 0 1 0.45-0.451h1.383a0.455 0.455 0 0 1 0.454 0.451v1.381a0.455 0.455 0 0 1-0.454 0.452h-1.383a0.451 0.451 0 0 1-0.45-0.452z"/><path transform="translate(-.019 11.44)" d="m8.012 29.789h1.388a3.167 3.167 0 0 0 3.163-3.16v-1.381a3.165 3.165 0 0 0-3.163-3.157h-1.388a3.164 3.164 0 0 0-3.161 3.157v1.381a3.165 3.165 0 0 0 3.161 3.16zm-0.451-4.543a0.451 0.451 0 0 1 0.451-0.451h1.388a0.451 0.451 0 0 1 0.451 0.451v1.381a0.452 0.452 0 0 1-0.451 0.451h-1.388a0.451 0.451 0 0 1-0.451-0.451z"/><path transform="translate(3.246,11.44)" d="m14.295 29.789h1.383a3.167 3.167 0 0 0 3.163-3.16v-1.381a3.166 3.166 0 0 0-3.163-3.157h-1.383a3.165 3.165 0 0 0-3.163 3.157v1.381a3.167 3.167 0 0 0 3.163 3.16zm-0.453-4.543a0.452 0.452 0 0 1 0.451-0.451h1.383a0.451 0.451 0 0 1 0.451 0.451v1.381a0.452 0.452 0 0 1-0.451 0.451h-1.383a0.451 0.451 0 0 1-0.451-0.451z"/><path transform="translate(6.512,11.44)" d="m20.577 29.789h1.383a3.166 3.166 0 0 0 3.161-3.16v-1.381a3.165 3.165 0 0 0-3.161-3.157h-1.383a3.166 3.166 0 0 0-3.164 3.157v1.381a3.169 3.169 0 0 0 3.164 3.16zm-0.453-4.543a0.452 0.452 0 0 1 0.45-0.451h1.383a0.454 0.454 0 0 1 0.454 0.451v1.381a0.454 0.454 0 0 1-0.454 0.451h-1.383a0.45 0.45 0 0 1-0.45-0.451z"/><path transform="translate(-.019 14.487)" d="m8.012 35.673h1.388a3.166 3.166 0 0 0 3.163-3.157v-1.383a3.165 3.165 0 0 0-3.163-3.157h-1.388a3.165 3.165 0 0 0-3.161 3.157v1.383a3.164 3.164 0 0 0 3.161 3.157zm-0.451-4.54a0.453 0.453 0 0 1 0.451-0.452h1.388a0.453 0.453 0 0 1 0.451 0.452v1.383a0.454 0.454 0 0 1-0.451 0.451h-1.388a0.453 0.453 0 0 1-0.451-0.451z"/><path transform="translate(3.246,14.487)" d="m14.295 35.673h1.383a3.166 3.166 0 0 0 3.163-3.157v-1.383a3.166 3.166 0 0 0-3.163-3.157h-1.383a3.166 3.166 0 0 0-3.163 3.157v1.383a3.165 3.165 0 0 0 3.163 3.157zm-0.453-4.54a0.454 0.454 0 0 1 0.451-0.452h1.383a0.453 0.453 0 0 1 0.451 0.452v1.383a0.454 0.454 0 0 1-0.451 0.451h-1.383a0.453 0.453 0 0 1-0.451-0.451z"/><path transform="translate(6.512,14.487)" d="m20.577 35.673h1.383a3.166 3.166 0 0 0 3.161-3.157v-1.383a3.165 3.165 0 0 0-3.161-3.157h-1.383a3.167 3.167 0 0 0-3.164 3.157v1.383a3.167 3.167 0 0 0 3.164 3.157zm-0.453-4.54a0.454 0.454 0 0 1 0.45-0.452h1.383a0.456 0.456 0 0 1 0.454 0.452v1.383a0.456 0.456 0 0 1-0.454 0.451h-1.383a0.452 0.452 0 0 1-0.45-0.451z"/><path transform="translate(9.779,8.392)" d="m26.858 23.9h1.383a3.166 3.166 0 0 0 3.159-3.156v-1.381a3.165 3.165 0 0 0-3.163-3.157h-1.379a3.165 3.165 0 0 0-3.158 3.157v1.381a3.165 3.165 0 0 0 3.158 3.156zm-0.451-4.54a0.454 0.454 0 0 1 0.451-0.451h1.383a0.455 0.455 0 0 1 0.454 0.451v1.381a0.455 0.455 0 0 1-0.454 0.452h-1.383a0.454 0.454 0 0 1-0.451-0.452z"/><path transform="translate(13.045,8.392)" d="m33.139 23.9h1.383a3.166 3.166 0 0 0 3.163-3.159v-1.378a3.163 3.163 0 0 0-3.163-3.157h-1.383a3.165 3.165 0 0 0-3.161 3.157v1.381a3.164 3.164 0 0 0 3.161 3.156zm-0.451-4.54a0.453 0.453 0 0 1 0.451-0.451h1.383a0.453 0.453 0 0 1 0.453 0.451v1.381a0.454 0.454 0 0 1-0.453 0.452h-1.383a0.453 0.453 0 0 1-0.451-0.452z"/></g></g></svg>',
            'users'                  => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1.6667 0 0 1.6667 38.333 0)"><path transform="translate(-184)" d="m191 0a7 7 0 1 0 7 7 7 7 0 0 0-7-7zm0 12a5 5 0 1 1 5-5 5 5 0 0 1-5 5z"/></g><g transform="matrix(1.6667 0 0 1.6667 30 25)"><path transform="translate(-144,-120)" d="m159 120h-6a9.01 9.01 0 0 0-9 9v17h4v19h2v-23h-2v2h-2v-15a7.008 7.008 0 0 1 7-7h6a7.008 7.008 0 0 1 7 7v15h-2v-2h-2v23h2v-19h4v-17a9.01 9.01 0 0 0-9-9z"/></g><g transform="translate(29,41)"><rect x="19.333" y="27.333" width="3.3333" height="31.667" stroke-width="1.6667"/></g><g transform="matrix(1.6667 0 0 1.6667 73.333 16.667)"><path transform="translate(-352,-80)" d="m358 80a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4z"/></g><g transform="matrix(1.6667 0 0 1.6667 73.333 38.333)"><path transform="translate(-352,-184)" d="m361 184h-9v2h9a5.006 5.006 0 0 1 5 5v13h-2v-2h-2v19h2v-15h4v-15a7.008 7.008 0 0 0-7-7z"/></g><g transform="matrix(1.6667 0 0 1.6667 66.667 68.333)"><path transform="translate(-320,-328)" d="m324 328v2h-4v2h4v15h2v-19z"/></g><g transform="matrix(1.6667 0 0 1.6667 81.667 75)"><rect width="2" height="15"/></g><g transform="matrix(1.6667 0 0 1.6667 6.6667 16.667)"><path transform="translate(-32,-80)" d="m38 80a6 6 0 1 0 6 6 6 6 0 0 0-6-6zm0 10a4 4 0 1 1 4-4 4 4 0 0 1-4 4z"/></g><g transform="matrix(1.6667 0 0 1.6667 0 38.333)"><path transform="translate(0,-184)" d="m7 184a7.008 7.008 0 0 0-7 7v15h4v15h2v-19h-2v2h-2v-13a5.006 5.006 0 0 1 5-5h9v-2z"/></g><g transform="matrix(1.6667 0 0 1.6667 23.333 68.333)"><path transform="translate(-112,-328)" d="m114 330v-2h-2v19h2v-15h4v-2z"/></g><g transform="matrix(1.6667 0 0 1.6667 15 75)"><rect width="2" height="15"/></g></svg>',
            'trolley'                => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1.6667 0 0 1.6667 -90 -1e-6)"><path d="m98.154 59.6c-2.7061 0-4.9077-2.1862-4.9077-4.8734 0-0.55442 0.09458-1.1011 0.2811-1.6248l0.19027-0.53421h-12.358l0.19028 0.53421c0.18654 0.52372 0.28112 1.0704 0.28112 1.6248 0 2.6872-2.2016 4.8734-4.9077 4.8734-2.7061 0-4.9077-2.1862-4.9077-4.8734 0-0.61689 0.11659-1.2208 0.34653-1.795l0.16712-0.4173-0.43389-0.1175c-2.1303-0.57693-3.6182-2.5102-3.6182-4.7015v-42.422c0-1.1898-0.97311-2.1578-2.1692-2.1578h-4.9077v-2.7156h4.9077c2.7061 0 4.9077 2.1862 4.9077 4.8734v42.422c0 1.1898 0.97311 2.1578 2.1692 2.1578h2.1692v-21.211h3.5385v-14.063h20.431v14.063h3.5385v21.211h3.5385v2.7156h-4.0101l0.19077 0.53446c0.18648 0.52246 0.28103 1.0686 0.28103 1.6233 0 2.6872-2.2016 4.8734-4.9077 4.8734zm-1.22e-4 -7.0312c-1.1961 0-2.1692 0.96799-2.1692 2.1578 0 1.1898 0.97312 2.1578 2.1692 2.1578 1.1961 0 2.1692-0.96799 2.1692-2.1578 0-1.1898-0.97311-2.1578-2.1692-2.1578zm-21.231 0c-1.1961 0-2.1692 0.96799-2.1692 2.1578 0 1.1898 0.97311 2.1578 2.1692 2.1578 1.1961 0 2.1692-0.96799 2.1692-2.1578 0-1.1898-0.97312-2.1578-2.1692-2.1578zm1.3692-21.211v18.495h22.031v-18.495h-6.1077v7.0313h-9.8154v-7.0313h-5.7077zm8.8461 0v4.3156h4.3385v-4.3156h-3.9385zm-5.3077-14.062v11.347h14.954v-11.347h-6.1077v3.5156h-2.7385v-3.5156h-5.7077z"/><path d="m61.8 0.8v1.9156h4.5077c1.4167 0 2.5692 1.1474 2.5692 2.5578v42.422c0 2.011 1.3664 3.7856 3.3228 4.3154l0.86779 0.23501-0.33425 0.83461c-0.21091 0.52664-0.31786 1.0805-0.31786 1.6462 0 2.4667 2.0221 4.4734 4.5077 4.4734s4.5077-2.0068 4.5077-4.4734c0-0.50854-0.08678-1.01-0.25794-1.4906l-0.38055-1.0684h13.492l-0.38053 1.0684c-0.17114 0.4805-0.25792 0.982-0.25792 1.4906 0 2.4667 2.0221 4.4734 4.5077 4.4734 2.4856 0 4.5077-2.0068 4.5077-4.4734 0-0.50876-0.0867-1.0097-0.25776-1.4889l-0.38152-1.0689h4.1776v-1.9156h-3.5385v-21.211h-3.5385v-14.062h-19.631v14.062h-3.5385v21.211h-2.5692c-1.4167 0-2.5692-1.1474-2.5692-2.5578v-42.422c0-2.4667-2.0221-4.4734-4.5077-4.4734h-4.5077m26.538 19.611h1.9385v-3.5156h6.9077v12.147h-15.754v-12.147h6.9077v3.5156m-3.5385 17.578h9.0154v-7.0312h6.9077v19.295h-22.831v-19.295h6.9077v7.0312m7.0769-1.9156h-5.1385v-5.1156h5.1385v5.1156m-14.954 21.211c-1.4167 0-2.5692-1.1474-2.5692-2.5578 0-1.4104 1.1526-2.5578 2.5692-2.5578 1.4167 0 2.5692 1.1474 2.5692 2.5578 0 1.4104-1.1526 2.5578-2.5692 2.5578m21.231 0c-1.4167 0-2.5692-1.1474-2.5692-2.5578 0-1.4104 1.1526-2.5578 2.5692-2.5578 1.4167 0 2.5692 1.1474 2.5692 2.5578 0 1.4104-1.1526 2.5578-2.5692 2.5578m-37.154-57.284h5.3077c2.9267 0 5.3077 2.3657 5.3077 5.2734v42.422c0 0.96926 0.79368 1.7578 1.7692 1.7578h1.7692v-21.211h3.5385v-14.062h21.231v14.062h3.5385v21.211h3.5385v3.5156h-3.8426c0.19638 0.5502 0.30431 1.1416 0.30431 1.7578 0 2.9078-2.381 5.2734-5.3077 5.2734-2.9267 0-5.3077-2.3657-5.3077-5.2734 0-0.61617 0.10791-1.2076 0.30429-1.759h-11.224c0.19639 0.55138 0.30431 1.1428 0.30431 1.759 0 2.9078-2.381 5.2734-5.3077 5.2734s-5.3077-2.3657-5.3077-5.2734c0-0.68637 0.13411-1.3417 0.3752-1.9437-2.2524-0.60996-3.9137-2.6591-3.9137-5.0876v-42.422c0-0.96926-0.79368-1.7578-1.7692-1.7578h-5.3077zm30.077 21.211h-3.5385v-3.5156h-5.3077v10.547h14.154v-10.547h-5.3077zm3.5385 17.578h-10.615v-7.0313h-5.3077v17.695h21.231v-17.695h-5.3077zm-3.5385-3.5156v-3.5156h-3.5385v3.5156zm-14.154 21.211c0.97556 0 1.7692-0.78855 1.7692-1.7578 0-0.96926-0.79368-1.7578-1.7692-1.7578-0.97556 0-1.7692 0.78855-1.7692 1.7578 0 0.96926 0.79367 1.7578 1.7692 1.7578zm21.231 0c0.97554 0 1.7692-0.78855 1.7692-1.7578 0-0.96926-0.79369-1.7578-1.7692-1.7578-0.97556 0-1.7692 0.78855-1.7692 1.7578 0 0.96926 0.79368 1.7578 1.7692 1.7578z" fill="#fff"/></g></svg>',
            'planet_earth'           => '<svg viewBox="0 0 100 100" xmlns="http://www.w3.org/2000/svg"><g transform="matrix(1.6491 0 0 1.6491 .54785 .47662)"><g transform="translate(43.199,53.793)"><path transform="translate(-368.63 -459.04)" d="m370.86 459.62a1.172 1.172 0 0 0-1.6-0.425l-0.045 0.026a1.172 1.172 0 1 0 1.176 2.027l0.045-0.026a1.172 1.172 0 0 0 0.424-1.602z" stroke="#fff" stroke-width=".6"/></g><path transform="translate(0 -.001)" d="m55.98 15.01a29.992 29.992 0 0 0-54.945 7.234 30 30 0 0 0 39.7 35.775 1.172 1.172 0 1 0-0.841-2.187 27.471 27.471 0 0 1-26.436-3.632l1.05-5.48 0.506-0.164a3.65 3.65 0 0 0 2.341-4.594l-0.371-1.142a1.294 1.294 0 0 1 0-0.808 3.646 3.646 0 0 0-2.764-4.712l-2.645-0.517-5.1-3.88a1.172 1.172 0 0 0-1.075-0.186l-2.988 0.971a27.626 27.626 0 0 1 0-3.2c0.118 0.11 0.195 0.184 0.242 0.233a1.228 1.228 0 0 0 1.145 0.587 17.021 17.021 0 0 0 2.1-0.239c0.989-0.146 1.972-0.311 1.982-0.312a1.172 1.172 0 0 0 0.774-1.816l-1.44-2.107 2.058-1.472a1.173 1.173 0 0 0 0.335-0.371l3.19-5.569 1.286-1.378a4.95 4.95 0 0 0 0.955-5.25 5.389 5.389 0 0 0-2.139-2.514 27.614 27.614 0 0 1 16.785-5.914l-2.092 2.625-7.76 2.72a1.171 1.171 0 0 0-0.7 0.659l-2.167 5.253a1.172 1.172 0 0 0 0.129 1.128l3.165 4.433a11.137 11.137 0 0 0-1.421 1.764c-0.231 0.328-0.449 0.638-0.6 0.811-0.131 0.151-0.262 0.3-0.392 0.445a10.966 10.966 0 0 0-1.885 2.616 6.983 6.983 0 0 0 0.579 7.059 6.577 6.577 0 0 0 6.21 2.665 9.124 9.124 0 0 0 1.145-0.231c1-0.249 1.323-0.273 1.645 0.028 0.157 0.147 0.171 0.175 0.168 0.673a4.555 4.555 0 0 0 0.1 1.161 3.7 3.7 0 0 0 0.99 1.618 2.626 2.626 0 0 1 0.425 0.542 2.918 2.918 0 0 1-0.142 2.424q-0.045 0.136-0.091 0.279c-0.53 1.64 0.209 3.267 0.86 4.7 0.212 0.466 0.411 0.906 0.542 1.295 1.054 3.126 1.843 3.842 2.477 4.131a2.442 2.442 0 0 0 1.023 0.217c1.689 0 3.452-1.569 4.262-2.563a3.6 3.6 0 0 0 0.725-1.706 1.868 1.868 0 0 1 0.15-0.524 3.07 3.07 0 0 1 0.384-0.494 4.065 4.065 0 0 0 0.958-1.592 2.579 2.579 0 0 1 0.8-1.248c0.081-0.086 0.167-0.177 0.259-0.278 1.561-1.7 1.159-3 0.733-4.369-0.358-1.156 0.268-1.9 1.783-3.306a12.657 12.657 0 0 0 1.861-2 2.53 2.53 0 0 0 0.5-2.347c-0.424-0.983-1.5-1.148-2.367-1.281a3.883 3.883 0 0 1-1.023-0.239 5.54 5.54 0 0 1-1.6-2.42c-0.085-0.19-0.17-0.378-0.255-0.561-0.152-0.326-0.3-0.719-0.466-1.136a9.83 9.83 0 0 0-1.732-3.226 7.484 7.484 0 0 0-3.363-1.494c-0.414-0.112-0.8-0.218-1.061-0.313a1.17 1.17 0 0 0-0.673-0.042 3.041 3.041 0 0 0-1.249 0.517 1.456 1.456 0 0 0-0.417 0.561 13.4 13.4 0 0 1-1.382-0.841l-0.023-0.015a1.661 1.661 0 0 0-0.1-1.128c-0.655-1.385-2.828-1.276-3.256-1.24-0.244 0.02-0.552 0.031-0.878 0.043-0.512 0.018-1.078 0.039-1.637 0.1l0.029-0.08a3 3 0 0 1 2.813-1.979h0.666a1.172 1.172 0 0 0 0-2.344h-0.666a5.349 5.349 0 0 0-4.536 2.53l-1.748-2.448 1.716-4.169 7.582-2.658a1.172 1.172 0 0 0 0.529-0.375l3.371-4.228a27.635 27.635 0 0 1 21.369 13.694 27.346 27.346 0 0 1 2.761 6.707l-0.5 0.443a3.13 3.13 0 0 0-1.043 2.277v0.028l-0.809-2.226a2.926 2.926 0 0 0-0.528-0.905l-1.444-1.69a2.937 2.937 0 0 0-2.236-1.031h-1.764a1.7 1.7 0 0 0-1.412 2.639l0.179 0.268a12.987 12.987 0 0 1-3.869 2.3l-2.422-4.64v-1.037a1.171 1.171 0 0 0-0.363-0.847l-2.181-2.083a1.173 1.173 0 0 0-0.421-0.258l-1.963-0.69a1.172 1.172 0 1 0-0.777 2.211l1.725 0.606 1.636 1.562v0.821a1.172 1.172 0 0 0 0.133 0.542l3.032 5.809a1.171 1.171 0 0 0 1.444 0.557l0.883-0.326a15.336 15.336 0 0 0 5.5-3.519 1.172 1.172 0 0 0 0.146-1.479l-0.062-0.094h0.56a0.6 0.6 0 0 1 0.454 0.209l1.444 1.69a0.6 0.6 0 0 1 0.107 0.184l1.593 4.381a1.172 1.172 0 0 0 1.93 0.428l0.758-0.758a3.658 3.658 0 0 0 0.968-1.761 27.749 27.749 0 0 1-10.3 25.236 1.172 1.172 0 1 0 1.447 1.843 30 30 0 0 0 7.423-38.569zm-50.439 18.125 4.832 3.674a1.171 1.171 0 0 0 0.485 0.217l2.912 0.569a1.3 1.3 0 0 1 0.987 1.684 3.622 3.622 0 0 0 0 2.263l0.371 1.142a1.3 1.3 0 0 1-0.836 1.641l-1.164 0.378a1.172 1.172 0 0 0-0.789 0.894l-0.939 4.892a27.958 27.958 0 0 1-5.338-6.658 27.5 27.5 0 0 1-3.4-9.759zm6.829-18.69-1.378 1.477a1.177 1.177 0 0 0-0.16 0.217l-3.132 5.461-2.781 1.994a1.172 1.172 0 0 0-0.287 1.613l1.041 1.526c-0.6 0.089-1.138 0.163-1.5 0.2-0.069-0.066-0.141-0.133-0.215-0.2-0.279-0.259-0.666-0.6-1.243-1.109a27.7 27.7 0 0 1 8.258-15.675 3.047 3.047 0 0 1 1.9 1.738 2.6 2.6 0 0 1-0.502 2.758zm9.554 6.4a7.514 7.514 0 0 1 2.621-0.369c0.355-0.013 0.69-0.024 0.99-0.05a3.187 3.187 0 0 1 0.5 0 1.172 1.172 0 0 0 0.539 1.458c0.22 0.118 0.526 0.322 0.85 0.538a6.752 6.752 0 0 0 2.929 1.365 1.944 1.944 0 0 0 1.592-0.663c0.017-0.017 0.033-0.034 0.05-0.05a1.255 1.255 0 0 0 0.314-0.384c0.187 0.054 0.384 0.107 0.586 0.162a7.8 7.8 0 0 1 2.271 0.84 8.318 8.318 0 0 1 1.251 2.462c0.176 0.456 0.343 0.886 0.529 1.285 0.08 0.171 0.159 0.348 0.239 0.526a7.318 7.318 0 0 0 2.454 3.423 4.844 4.844 0 0 0 1.95 0.594l0.255 0.04a11.331 11.331 0 0 1-1.487 1.56c-1.451 1.351-3.256 3.032-2.425 5.715 0.392 1.267 0.411 1.4-0.22 2.09-0.086 0.094-0.166 0.179-0.241 0.259a4.815 4.815 0 0 0-1.321 2.143 1.829 1.829 0 0 1-0.477 0.745 5.232 5.232 0 0 0-0.656 0.868 3.8 3.8 0 0 0-0.424 1.245 1.435 1.435 0 0 1-0.245 0.687 6.831 6.831 0 0 1-1.479 1.308 1.933 1.933 0 0 1-0.982 0.391 8.36 8.36 0 0 1-1.262-2.752c-0.169-0.5-0.4-1.017-0.629-1.515-0.5-1.1-1.015-2.236-0.764-3.013l0.088-0.269a5.119 5.119 0 0 0 0.078-4.057 4.226 4.226 0 0 0-0.853-1.216 2.178 2.178 0 0 1-0.448-0.594 2.533 2.533 0 0 1-0.038-0.593 2.906 2.906 0 0 0-0.91-2.4 3.775 3.775 0 0 0-3.81-0.592 7.351 7.351 0 0 1-0.855 0.178 4.21 4.21 0 0 1-4.034-1.705 4.639 4.639 0 0 1-0.364-4.69 8.9 8.9 0 0 1 1.517-2.06c0.136-0.153 0.273-0.308 0.411-0.466 0.229-0.263 0.48-0.62 0.746-1a8.471 8.471 0 0 1 1.165-1.44z" stroke="#fff" stroke-width=".6"/><g transform="translate(41.033,37.349)"><path transform="translate(-350.15 -318.71)" d="m357.17 321.71-0.564-2.125a1.172 1.172 0 0 0-2.163-0.258c-0.128 0.237-0.244 0.488-0.356 0.73a5.038 5.038 0 0 1-0.585 1.063 2.588 2.588 0 0 1-0.688 0.408 3.222 3.222 0 0 0-1.952 1.9 3.433 3.433 0 0 0-0.078 1.653 1.067 1.067 0 0 1-0.03 0.691l-0.012 0.027c-0.373 0.875-1.067 2.5-0.094 3.89a2.441 2.441 0 0 0 1.992 1.243 1.9 1.9 0 0 0 0.312-0.025c1.2-0.2 2.094-1.439 2.828-3.913l1.378-4.645a1.171 1.171 0 0 0 0.012-0.639zm-3.634 4.613a7.108 7.108 0 0 1-0.9 2.112c-0.022-0.028-0.046-0.06-0.071-0.1-0.224-0.319 0.111-1.1 0.332-1.625l0.012-0.027a3.3 3.3 0 0 0 0.192-1.952 1.229 1.229 0 0 1 0-0.608c0.046-0.146 0.32-0.288 0.734-0.488 0.183-0.089 0.385-0.186 0.589-0.3z" stroke="#fff" stroke-width=".6"/></g><g transform="translate(30.005,13.835)"><path transform="translate(-256.04 -118.06)" d="m257.68 118.15-0.079-0.028a1.172 1.172 0 1 0-0.776 2.211l0.079 0.028a1.172 1.172 0 1 0 0.776-2.211z" stroke="#fff" stroke-width=".6"/></g></g></svg>',
        ];
    }

    function index()
    {
        /** @var Video_Model $videoModel */
        $videoModel = model(Video_Model::class);

        $this->breadcrumbs[] = [
            'link' 	=> __SITE_URL . 'about',
            'title'	=> translate('about_us_nav_about_us', null, true),
        ];

        views()->assign([
            'navActive'             => 'about',
            'hash'                  => request()->query->get('request'),
            'breadcrumbs'           => $this->breadcrumbs,
            'videos'	            => $videoModel->getVideos(['short_names' => ['about_page']]),
            'videosList'            => $videoModel->getVideos(['short_names' => ['about_videos']]),
            'icons'                 => $this->icons(),
            'googleAnalyticsEvents' => true,
            'webpackData'           => [
                'styleCritical' => 'about_us',
                'pageConnect'   => 'about_us_page',
            ],
            'templateViews'         => [
                'customEncoreLinks' => true,
                'headerOutContent'  => 'about/about_us/header_view',
                'mainOutContent'    => 'about/about_us/index_view',
            ],
        ]);

        views()->display('new/template_views/index_view');
    }


	function team()
	{
		show_404();

		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
			),
			array(
				'link' 	=> __SITE_URL . 'about/team',
				'title'	=> translate('about_us_nav_executive_team', null, true)
			),
		);

        /** @var Hiring_Model $vacancyModel */
        $vacancyModel = model(Hiring_Model::class);
        $vacancies_list = $vacancyModel->get_vacancies(array('visible' => 1));

        foreach ($vacancies_list as &$vacancy) {
            $vacancy['photoUrl'] = $this->storage->url(VacancyPathGenerator::imageUploadPath($vacancy['id_vacancy'],$vacancy['photo']));
        }

        $ourTeam = model('our_team')->get_persons(array('order_by' => 'person_weight ASC'));
        foreach ($ourTeam as &$info) {
            $info['imageUrl'] = $this->storage->url(OurTeamFilesPathGenerator::defaultPublicImagePath($info['img_person']));
        }

		$data = array(
			'header_out_content'	=> 'new/about/executive_team/header_view',
			'footer_out_content'	=> 'new/about/bottom_investor_view',
			'vacancies_list'		=>  $vacancies_list,
			'main_content'			=> 'new/about/executive_team/index_view',
			'breadcrumbs'			=> $this->breadcrumbs,
			'nav_active'			=> 'executive team',
			'our_team'				=> $ourTeam,
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function culture_and_policy()
	{
		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
			),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_nav_culture_and_policy', null, true)
			),
		);

		$data = array(
			'header_out_content'	=> 'new/about/culture_and_policy/header_view',
			'footer_out_content'	=> 'new/about/culture_and_policy/bottom_view',
			'main_content'			=> 'new/about/culture_and_policy/index_view',
			'breadcrumbs'			=> $this->breadcrumbs,
			'nav_active'			=> 'culture and policy',
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function in_the_news()
	{
        $updatesModel = model(Ep_Updates_Model::class);
        $massMediaModel = model(Mass_media_Model::class);
        $newsModel = model(Ep_News_Model::class);

		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
			),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_nav_in_the_news', null, true)
			),
		);

		$limit = " 0,4 ";
		$updatesLimit = 4;

        $newsList = $massMediaModel->get_news(['lang' => __SITE_LANG, 'limit' => $limit, 'published' => 1, 'order_by' => 'date_news DESC']);

        foreach ($newsList as &$news) {
            $news['imageUrl'] = $this->storage->url(MassMediaFilesPathGenerator::defaultNewsPublicImagePath($news['img_news'] ?: 'no-mage.jpg'));
            $news['logoUrl'] = $this->storage->url(MassMediaFilesPathGenerator::defaultMediaPublicImagePath($news['logo_media'] ?: 'no-mage.jpg'));
        }

        $epNews = $newsModel->get_list_ep_news_public(['limit' => $limit]);

        foreach ($epNews as &$epOneNews) {
            $epOneNews['imageUrl'] = $this->storage->url(EpNewsImagePathGenerator::defaultPublicImagePath($epOneNews['main_image'] ?: 'no-mage.jpg'));
        }

        /** @var Ep_News_Archive_Model $newsArchiveModel */
        $newsArchiveModel = model(Ep_News_Archive_Model::class);

        $hash = request()->query->get('hash');
        $hashList = ['press_releases', 'ep_news', 'ep_updates', 'newsletter_archive'];
        $headerData = [
            'press_releases'     => [
                'header_title' => translate('about_us_in_the_news_press_releases_header_title'),
                'header_img'   => 'press_releases_header.jpg',
            ],
            'ep_news'            => [
                'header_title' => translate('about_us_in_the_news_news_header_title'),
                'header_img'   => 'in_the_news_header3.jpg',
            ],
            'ep_updates'         => [
                'header_title' => translate('about_us_in_the_news_updates_header_title'),
                'header_img'   => 'updates_header.jpg',
            ],
            'newsletter_archive' => [
                'header_title' => translate('about_us_in_the_news_newsletter_archive_header_title'),
                'header_img'   => 'newsletter_archive_header.jpg',
            ],
        ];

        $issetHash = !empty($hash) && in_array($hash, $hashList);
		$data = array(
			'apply_channel_link_tpl'	=> 'channel/' . config('replace_uri_template'),
			'newsletter_archive'    	=> $newsArchiveModel->findAllBy([
                'limit' => $updatesLimit,
                'order' => ['published_on' => 'DESC']
            ]),
			'header_out_content'		=> 'new/about/in_the_news/header_view',
			'footer_out_content'		=> 'new/about/bottom_who_we_are_view',
            'main_content'				=> 'new/about/in_the_news/index_view',
			'breadcrumbs'				=> $this->breadcrumbs,
			'nav_active'				=> 'in the news',
			'ep_updates'				=> $updatesModel->get_list_ep_update_public(['per_p' => $updatesLimit, 'from' => 0]),
			'news_list'					=> $newsList,
            'ep_news'					=> $epNews,
            'header_title'              => $issetHash ? $headerData[$hash]['header_title'] : translate('about_us_in_the_news_press_releases_header_title'),
            'header_img'                => $issetHash ? $headerData[$hash]['header_img'] : 'press_releases_header.jpg',
            'pageHash'                  => $hash ?? null,
        );

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function members_protect()
	{
		headerRedirect(__SITE_URL . 'about/partnership', 301);
	}

	public function seller_verification()
	{
        /** @var Verification_Document_Types_Model $verificationDocumentTypesModel */
        $verificationDocumentTypesModel = model(Verification_Document_Types_Model::class);

		$this->breadcrumbs = [
			[
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
            ],
			[
				'link' 	=> '',
				'title'	=> translate('about_us_nav_seller_verification', null, true)
            ],
		];

        $documents = $verificationDocumentTypesModel->runWithoutAllCasts(
            fn () => $verificationDocumentTypesModel->findAllBy([
                'conditions' => [
                    'documentGroupsIds' => [2, 3, 5, 6],
                ],
                'joins' => [
                    'verificationDocsGroupsRelationInnerJoin'
                ],
                'group' => [
                    "`{$verificationDocumentTypesModel->getTable()}`.`id_document`"
                ],
            ])
        );

        $dataTable = [];
        foreach ($documents as $document) {
            $doc = [
                'dt_documents'              => $document['document_title'],
                'dt_verified_seller'        => $this->xIcon,
                'dt_certified_seller'       => $this->xIcon,
                'dt_verified_manufacturer'  => $this->xIcon,
                'dt_certified_manufacturer' => $this->xIcon,
                'count'                     => 0,
            ];

            $groups = [
                '2' => 'dt_verified_seller',
                '3' => 'dt_certified_seller',
                '5' => 'dt_verified_manufacturer',
                '6' => 'dt_certified_manufacturer',
            ];


            foreach(explode(',', $document['document_groups']) as $group){
                if(array_key_exists($group, $groups)){
                    array_push($groups, $group);
                    $doc[$groups[$group]] = $this->vIcon;
                    $doc["count"] += 1;
                }
            }
            array_push($dataTable, $doc);
        }

        usort($dataTable, function ($a, $b) {
			return ($a['count'] >= $b['count']) ? -1 : 1;
        });

        if (!logged_in()) {
            array_push($dataTable, [
                'dt_documents'              => '',
                'dt_verified_seller'        => $this->generateButton('seller'),
                'dt_certified_seller'       => $this->generateButton('seller'),
                'dt_verified_manufacturer'  => $this->generateButton('manufacturer'),
                'dt_certified_manufacturer' => $this->generateButton('manufacturer'),
            ]);
        }

		$data = [
			'header_out_content' => 'new/about/seller_verification/header_view',
			'footer_out_content' => 'new/about/bottom_need_help_view',
			'main_content'		 => 'new/about/seller_verification/index_view',
			'breadcrumbs'		 => $this->breadcrumbs,
            'nav_active'		 => 'seller verification',
            'dataTable'          => $dataTable,
            'isLogged'           => logged_in(),
		];

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
    }


	public function certification_and_upgrade_benefits()
	{
		$this->breadcrumbs = [
			[
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
            ],
			[
				'link' 	=> '',
				'title'	=> translate('about_us_nav_certification_benefits', null, true)
            ],
        ];

        $dataTable = [];
        for($i=1; $i<25; $i++) {
            array_push($dataTable, [
                "dt_feature"                => translate("about_us_certification_and_upgrade_benefits_table_row_{$i}_title"),
                "dt_verified_seller"        => $i < 6 ? $this->vIcon : $this->xIcon,
                "dt_verified_manufacturer"  => $i > 8 ? $this->xIcon : $this->vIcon,
                "dt_certified_seller"       => $i > 5 && $i < 9 ? $this->xIcon : $this->vIcon,
                "dt_certified_manufacturer" => $this->vIcon
            ]);
        }

        if(!logged_in()){
            array_push($dataTable, [
                "dt_feature"                => "",
                "dt_verified_seller"        => $this->generateButton("seller"),
                "dt_verified_manufacturer"  => $this->generateButton("manufacturer"),
                "dt_certified_seller"       => $this->generateButton("seller"),
                "dt_certified_manufacturer" => $this->generateButton("manufacturer"),
            ]);
        }

		$data = [
			'header_out_content' => 'new/about/certification_and_upgrade_benefits/header_view',
			'footer_out_content' => 'new/about/bottom_become_member_view',
			'main_content'       => 'new/about/certification_and_upgrade_benefits/index_view',
			'breadcrumbs'        => $this->breadcrumbs,
            'nav_active'         => 'certification and upgrade benefits',
            'dataTable'          => $dataTable,
            'isLogged'           => logged_in(),
        ];

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function integrity_and_compliance()
	{
		headerRedirect(__SITE_URL . 'about/culture_and_policy', 301);
	}

	function international_standards()
	{
		// redirect 301 - on 04.20.2018
		headerRedirect('/library_international_standards', 301);
	}

	function our_story()
	{
		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
			),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_nav_our_story', null, true)
			),
		);

		$data = array(
			'footer_out_content'	=> 'new/about/bottom_who_we_are_view',
			'header_out_content'	=> 'new/about/our_story/header_view',
			'main_content'			=> 'new/about/our_story/index_view',
			'breadcrumbs'			=> $this->breadcrumbs,
			'nav_active'			=> 'our_story',
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	public function link_to_us()
	{
		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
			),
			array(
				'link' 	=> __SITE_URL . 'about/partnership',
				'title'	=> translate('about_us_nav_partnership', null, true)
			),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_nav_link_to_us', null, true)
			),
		);

		$banners = model('banner')->get_banners();

		$data = array(
			'sidebar_right_content'	=> 'new/about/link_to_us/sidebar_view',
			'header_out_content'	=> 'new/about/link_to_us/header_view',
			'footer_out_content'	=> 'new/about/link_to_us/bottom_view',
			'banners_by_type'		=> arrayByKey($banners, 'type', true),
			'banner_types'			=> $this->banner->get_types(),
			'banners_list'			=> $banners,
			'main_content'			=> 'new/about/link_to_us/index_view',
			'breadcrumbs'			=> $this->breadcrumbs,
			'nav_active'			=> 'partnership',
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function partnership()
	{
		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us')
			),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_nav_partnership')
			),
		);

        /** @var Partners_Model $partnersModel */
        $partnersModel = model(Partners_Model::class);
        $otherPartners = $partnersModel->get_partners(array('limit' => 6));

        foreach ($otherPartners as &$otherPartner) {
            $otherPartner['image'] = $this->storage->url(PartnersUploadsPathGenerator::publicPromoBannerPath($otherPartner['img_partner']));
        }

		$data = array(
			'header_out_content'	=> 'new/about/partnership/header_view',
			'footer_out_content'	=> 'new/about/partnership/bottom_view',
			'main_content'			=> 'new/about/partnership/index_view',
			'breadcrumbs'			=> $this->breadcrumbs,
			'nav_active'			=> 'partnership',
            'otherPartners'         => $otherPartners,
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function other_partners()
	{
		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us')
			),
			array(
				'link' 	=> __SITE_URL . 'about/partnership',
				'title'	=> translate('about_us_nav_partnership')
			),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_nav_other_partners')
			),
		);

        /** @var Partners_Model $partnersModel */
        $partnersModel = model(Partners_Model::class);
        $otherPartners = $partnersModel->get_partners();

        foreach ($otherPartners as &$otherPartner) {
            $otherPartner['image'] = $this->storage->url(PartnersUploadsPathGenerator::publicPromoBannerPath($otherPartner['img_partner']));
        }

		$data = array(
			'header_out_content'	=> 'new/about/other_partners/header_view',
			'footer_out_content'	=> 'new/about/other_partners/bottom_view',
			'main_content'			=> 'new/about/other_partners/index_view',
			'breadcrumbs'			=> $this->breadcrumbs,
			'nav_active'			=> 'partnership',
			'office' 				=> model('offices')->get_office(1),
            'otherPartners'         => $otherPartners,
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	/**
	 * @deprecated
	 */
	function ashwood_partnerships(){
		show_404();

		$this->breadcrumbs = array(
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_ashwood_partnerships_breadcrumb', null, true)
			)
		);

		$data = array(
			'main_content'	=> 'new/about/static_pages/ashwood_partnerships_view',
			'breadcrumbs'	=> $this->breadcrumbs,
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function country_focus_colombia()
	{
		$this->breadcrumbs = array(
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_country_focus_colombia_breadcrumb', null, true)
			),
		);

		$data = array(
			'breadcrumbs'	=> $this->breadcrumbs,
			'main_content'	=> 'new/about/static_pages/country_focus_colombia_view'
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	function vacancy()
	{
        show_404();

		$id_record = intval($this->uri->segment(3));

		$this->load->model('Hiring_Model', 'hiring');

		$data['vacancy'] = $this->hiring->get_vacancy($id_record);
		$data['vacancies_list'] = $this->hiring->get_vacancies(array('visible' => 1, 'not_id_vacancy' => $id_record, 'limit' => 2));
		if (!$id_record || empty($data['vacancy'])) {
			show_404();
		}

		$this->breadcrumbs[] = array(
			'link' 	=> __SITE_URL . 'about',
			'title'	=> translate('about_us_nav_about_us')
		);

		$this->breadcrumbs[] = array(
			'link' 	=> __SITE_URL . 'about/team',
			'title'	=> translate('about_us_nav_executive_team')
		);

		$this->breadcrumbs[] = array(
			'link' 	=> '',
			'title'	=> $data['vacancy']['post_vacancy']
		);

		$data['breadcrumbs'] = $this->breadcrumbs;
        $data['vacancy']['photoUrl'] = $this->storage->url(VacancyPathGenerator::imageUploadPath($data['vacancy']['id_vacancy'], $data['vacancy']['photo']));

        foreach ($data['vacancies_list'] as &$vacancy) {
            if (empty($vacancy['photo'])) {
                $vacancy['photoUrl'] =  __IMG_URL . "public/img/no_image/no-image-125x90.png";
            }else {
                $vacancy['photoUrl'] = $this->storage->url(VacancyPathGenerator::imageUploadPath($vacancy['id_vacancy'], $vacancy['photo']));
            }
        }

		$data['current_page'] = "vacancy";
		$data['main_content'] = 'new/about/vacancy/index_view';
		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

	public function features()
	{
		$this->breadcrumbs = array(
			array(
				'link' 	=> __SITE_URL . 'about',
				'title'	=> translate('about_us_nav_about_us', null, true)
			),
			array(
				'link' 	=> '',
				'title'	=> translate('about_us_nav_ep_features', null, true)
			),
		);

		$data = array(
			'footer_out_content'	=> 'new/about/features/bottom_view',
			'header_out_content'	=> 'new/about/features/header_view',
			'main_content'			=> 'new/about/features/index_view',
			'breadcrumbs' 			=> $this->breadcrumbs,
			'nav_active'			=> 'features',
            'googleAnalyticsEvents' => true,
		);

		$this->view->assign($data);
		$this->view->display('new/index_template_view');
	}

    public function why_exportportal()
    {
        /** @var Ep_Reviews_Model $epReviewsModel */
        $epReviewsModel = model(Ep_Reviews_Model::class);

        views()->assign([
            'navActive'    => 'why exportportal',
            'breadcrumbs'  => [
                [
                    'link'  => __SITE_URL . 'about',
                    'title' => 'About us'
                ],
                [
                    'link'  => __SITE_URL . 'why_exportportal',
                    'title' => 'Why Export Portal'
                ]
            ],
            'epReviews'  => $epReviewsModel->findAllBy([
                'conditions'    => ['is_published' => 1],
                'with'          => ['user'],
                'order'         => ["{$epReviewsModel->getTable()}.`published_date`" => 'desc'],
                'limit'         => 12,
            ]),
            'icons'        => $this->icons(),
            'googleAnalyticsEvents' => true,
            'webpackData'  => [
                'styleCritical'     => 'why_exportportal',
                'pageConnect'       => 'why_ep_page'
            ],
            'templateViews'         => [
                'headerOutContent' => 'about/why_exportportal/header_view',
                'mainOutContent'   => 'about/why_exportportal/index_view',
            ]
        ]);

        views()->display('new/template_views/index_view');
    }
}
