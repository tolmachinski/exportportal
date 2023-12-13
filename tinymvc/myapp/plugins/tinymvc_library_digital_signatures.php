<?php

declare(strict_types=1);

use App\Common\DigitalSignature\Configuration;
use App\Common\DigitalSignature\Provider\NullProviderFactory;
use App\Common\DigitalSignature\Provider\ProviderFactoryInterface;
use App\Common\DigitalSignature\Provider\ProviderInterface;
use App\Common\DigitalSignature\Provider\ProviderResolverInterface;
use App\Common\DigitalSignature\SigningProviderFactoryInterface;
use App\DigitalSignature\Provider\DocuSign\DocuSignFactory;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;

/**
 * Library Digital_Signatures.
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/g.-Digital-Signatures
 */
class TinyMVC_Library_Digital_Signatures implements ProviderResolverInterface
{
	private const CONFIG_FILE = 'digital_signatures.php';

	/**
	 * The list of known digital signatures servies factories.
	 *
	 * @var string[]
	 */
	private array $factories = [
		NullProviderFactory::class,
		DocuSignFactory::class,
	];

	/**
	 * An array of collaborators that may be used to extend the service default behaviour.
	 * Individual services may require different collaborators, as needed.
	 */
	private array $collaborators = [];

	/**
	 * The list of intialiezed services.
	 *
	 * @var ProviderInterface[]
	 */
	private array $services = [];

	/**
	 * The digital signature configurations.
	 */
	private array $configs;

	/**
	 * The configurations.
	 */
	private Configuration $configurations;

	/**
	 * The list of provider factories.
	 *
	 * @var ProviderFactoryInterface[]
	 */
	private array $resolvedFactories = [];

	/**
	 * The service definitions.
	 */
	private array $serviceDefinitons = [];

	/**
	 * Library Digital_Signatures constructor.
	 */
	public function __construct()
	{
		$fileLocator = new FileLocator([\App\Common\CONFIG_PATH]);
		$phpConfigFile = $fileLocator->locate(static::CONFIG_FILE, null, true);
		$this->configurations = new Configuration();
		$this->configs = (new Processor())->processConfiguration(
			$this->configurations,
			(function () use ($phpConfigFile): array {
				if (!file_exists($phpConfigFile)) {
					return ['digital_signatures' => []];
				}

				return ['digital_signatures' => include $phpConfigFile];
			})()
		);

		$this->serviceDefinitons = $this->configs['services'];
		$this->collaborators = [
			'oauth2_clients' => library(TinyMVC_Library_Oauth2::class),
		];
	}

	/**
	 * Resolves the service by its name.
	 */
	public function resolve(string $type): ?ProviderInterface
	{
		if (!isset($this->serviceDefinitons[$type])) {
			return null;
		}

		if (!isset($this->services[$type])) {
			if (null === $service = $this->makeService($this->serviceDefinitons[$type])) {
				throw new RuntimeException('Failed to create the digital signature service.');
			}

			$this->services[$type] = $service;
		}

		return $this->services[$type];
	}

	/**
	 * Makes the service instance.
	 */
	private function makeService(array $serviceDefinitons): ?ProviderInterface
	{
		$serviceName = $serviceDefinitons['type'];
		/** @var SigningProviderFactoryInterface $factory */
		foreach ($this->findFactoriesForType($serviceName) as $factory) {
			$service = $factory->create($serviceDefinitons['options'] ?? [], $this->collaborators);
			if (null === $service) {
				continue;
			}

			return $service;
		}

		return null;
	}

	/**
	 * Gets the list of factories.
	 *
	 * @return ProviderFactoryInterface[]
	 */
	private function getFactories(): array
	{
		if (empty($this->resolvedFactories)) {
			$this->spawnFactories();
		}

		return $this->resolvedFactories;
	}

	/**
	 * Finds the supported provider factories by its name.
	 */
	private function findFactoriesForType(string $serviceName): Generator
	{
		yield from array_filter(
			$this->getFactories(),
			fn (ProviderFactoryInterface $factory) => $factory->supports($serviceName)
		);
	}

	/**
	 * Spawns the factories.
	 */
	private function spawnFactories(): void
	{
		foreach ($this->factories as $factoryName) {
			if (!\is_a($factoryName, ProviderFactoryInterface::class, true)) {
				throw new InvalidArgumentException('Invalid factory name provided.');
			}

			$this->resolvedFactories[] = new $factoryName();
		}
	}
}

// End of file tinymvc_library_digital_signatures.php
// Location: /tinymvc/myapp/plugins/tinymvc_library_digital_signatures.php
