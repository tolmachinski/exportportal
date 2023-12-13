<?php

use App\Common\DependencyInjection\ServiceLocator\LibraryLocator;
use App\Logger\ActivityLogger;
use App\Logger\CommunicationLogger;
use App\Logger\Handlers\CallableHandler;
use Monolog\Logger;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [01.12.2021]
 * library refactoring: old model used
 *
 * @link https://gitlab.usalinksystem.net/house88/exportportal/-/wikis/c.-Backend/d.-Libraries/a.-Activity-Logger
 */
class TinyMVC_Library_Activity_Logger extends ActivityLogger
{
	protected ContainerInterface $container;

	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
		parent::__construct([$this->getDatabaseWriteHandler()]);

		$session = $container->get(LibraryLocator::class)->get(TinyMVC_Library_Session::class);
		if ($session->loggedIn) {
			if ('users_staff' !== $session->user_type) {
				$id = $session->id;
			} else {
				$id = $session->my_seller;
			}

			$this->setInitiator($id);
		}
	}

	/**
	 * Creates write callable.
	 *
	 * @return CallableHandler
	 */
	private function getDatabaseWriteHandler()
	{
		$dbl = $this->getDefaultDataLayer();

		return new CallableHandler(function (array $logRecord) use ($dbl) {
			if (null === $dbl) {
				return false;
			}

			$logRecord['date'] = $logRecord['datetime'];
			$logRecord['level_name'] = $logRecord['levelName'];
			$logRecord['id_resource'] = $logRecord['resource']['id'];
			$logRecord['id_initiator'] = $logRecord['initiator']['id'];
			$logRecord['id_resource_type'] = $logRecord['resource']['type'];
			$logRecord['id_operation_type'] = $logRecord['operation']['type'];
			unset($logRecord['datetime'], $logRecord['levelName'], $logRecord['initiator'], $logRecord['resource'], $logRecord['operation']);

			//region monolog

			$logger = new Logger('CommunicationLogger');
			$logger->pushHandler(new CommunicationLogger('monolog_logs', $dbl->db));
			$logger->error("Action: \"{$logRecord['message']}\"", [
				'id_user'     => $logRecord['id_initiator'],
				'type'        => 'action',
				'id_resource' => $logRecord['id_resource'],
				'details'     => $logRecord['context'],
			]);
			//endregion monolog

			return $dbl->write_log($logRecord);
		});
	}

	/**
	 * Returns default data layer.
	 *
	 * @return \Activity_Log_Model
	 */
	private function getDefaultDataLayer()
	{
		return \model(Activity_Logs_Model::class, 'activity');
	}
}
