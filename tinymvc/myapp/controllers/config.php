<?php

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Config_Controller extends TinyMVC_Controller
{
    public function administration()
    {
        checkAdmin('manage_content');
        views(['admin/header_view', 'admin/config/index_view', 'admin/footer_view'], ['title' => 'Config']);
    }

    public function ajax_configs_dt()
    {
        checkIsAjax();
        checkIsLoggedAjaxDT();
        checkPermisionAjaxDT('manage_content');

        $request = request();
        /** @var Configs_Model $configs */
        $configs = model(Configs_Model::class);
        $perPage = $request->request->getInt('iDisplayLength', 10);
        $offset = $request->request->getInt('iDisplayStart', 0);
        $ordering = array_column(dtOrdering($request->request->all(), ['dt_key' => 'key_config', 'dt_value' => 'value']), 'direction', 'column');
        $conditions = dtConditions($request->request->all(), [['as' => 'search', 'key' => 'keywords', 'type' => 'cleanInput']]);
        $paginator = $configs->paginate(['conditions' => $conditions, 'order' => $ordering], $perPage, $offset / $perPage + 1);
        $entries = [];
        foreach ($paginator['data'] as $entry) {
            $entries[] = [
                'dt_key'         => $entry['key_config'],
                'dt_value'       => $entry['value'],
                'dt_description' => $entry['description'],
                'dt_actions'     => [
                    sprintf(
                        '<a class="ep-icon ep-icon_pencil fancyboxValidateModalDT fancybox.ajax" title="Update complain" href="%s" data-title="Edit config"></a>',
                        getUrlForGroup('config/popup_forms/edit_config/' . cleanOutput($entry['key_config']))
                    ),
                    sprintf(
                        '<a class="ep-icon ep-icon_remove txt-red confirm-dialog" data-callback="remove_config" data-message="%s"  title="%s" data-config="%s"></a>',
                        'Are you sure you want to delete this config?',
                        'Delete config',
                        cleanOutput($entry['key_config'])
                    ),
                ],
            ];
        }

        jsonResponse('', 'success', [
            'sEcho'                => request()->request->getInt('sEcho', 0),
            'iTotalRecords'        => $paginator['total'] ?? 0,
            'iTotalDisplayRecords' => $paginator['total'] ?? 0,
            'aaData'               => $entries,
        ]);
    }

    public function popup_forms()
    {
        checkIsAjax();
        checkIsLoggedAjaxModal();
        checkPermisionAjaxModal('manage_content');

        switch (uri()->segment(3)) {
            case 'add_config':
                views()->display('admin/config/config_form_view');

            break;
            case 'edit_config':
                /** @var Configs_Model $configs */
                $configs = model(Configs_Model::class);

                views()->display('admin/config/config_form_view', [
                    'config' => $configs->findOneBy([
                        'scopes' => [
                            'key' => cleanInput(uri()->segment(4)),
                        ],
                    ]),
                ]);

            break;
        }
    }

    public function ajax_config_operation()
    {
        checkIsAjax();
        checkIsLoggedAjax();
        checkPermisionAjax('manage_content');

        $request = request();

        switch (uri()->segment(3)) {
            case 'delete_config':
                /** @var Configs_Model $configs */
                $configs = model(Configs_Model::class);
                $entryKey = $request->request->get('config');
                $entry = $configs->findOneBy(['scopes' => ['key' => $entryKey]]);
                if (null === $entry) {
                    jsonResponse(sprintf('The configuration entry with key "%s" doesn\'t exist.', $entryKey));
                }

                if (!$configs->deleteOne($entry['id_config'])) {
                    jsonResponse(sprintf('Failed to delete the configuration entry with key "%s".', $entryKey));
                }

                try {
                    $this->generateConfigCache();
                } catch (\Throwable $e) {
                    // Silently fail.
                }

                jsonResponse('Configuration entry was deleted successfully.', 'success');

            break;
            case 'add_config':
                $validator_rules = [
                    [
                        'field' => 'key_config',
                        'label' => 'Key',
                        'rules' => ['required' => '', 'valid_var_name' => ''],
                    ],
                    [
                        'field' => 'value',
                        'label' => 'Value',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => ['required' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Configs_Model $configs */
                $configs = model(Configs_Model::class);
                $entryKey = $request->request->get('key_config');
                $entry = $configs->findOneBy(['scopes' => ['key' => $entryKey]]);
                if (null !== $entry) {
                    jsonResponse(sprintf('The configuration entry with key "%s" already exists.', $entryKey));
                }

                if (
                    !$configs->insertOne([
                        'value'       => cleanInput($request->request->get('value')),
                        'description' => cleanInput($request->request->get('description')),
                        'key_config'  => $entryKey,
                    ])
                ) {
                    jsonResponse('Failed to add new configuration entry. Please try again later.');
                }

                try {
                    $this->generateConfigCache();
                } catch (\Throwable $e) {
                    // Silently fail.
                }

                jsonResponse('The new configuration entry has been successfully created.', 'success');

            break;
            case 'edit_config':
                $validator_rules = [
                    [
                        'field' => 'key_config',
                        'label' => 'Key',
                        'rules' => ['required' => '', 'valid_var_name' => ''],
                    ],
                    [
                        'field' => 'value',
                        'label' => 'Value',
                        'rules' => ['required' => ''],
                    ],
                    [
                        'field' => 'description',
                        'label' => 'Description',
                        'rules' => ['required' => ''],
                    ],
                ];
                $this->validator->set_rules($validator_rules);
                if (!$this->validator->validate()) {
                    jsonResponse($this->validator->get_array_errors());
                }

                /** @var Configs_Model $configs */
                $configs = model(Configs_Model::class);
                $entryKey = $request->request->get('key_config');
                $entry = $configs->findOneBy(['scopes' => ['key' => $entryKey]]);
                if (null === $entry) {
                    jsonResponse(sprintf('The configuration entry with key "%s" does not exists.', $entryKey));
                }

                if (
                    !$configs->updateOne($entry['id_config'], [
                        'value'       => cleanInput($request->request->get('value')),
                        'description' => cleanInput($request->request->get('description')),
                    ])
                ) {
                    jsonResponse(sprintf('Failed to update the configuration entry with key "%". Please try again later.', $entryKey));
                }

                // TODO: why this code is here?!
                if ('item_thumbs_size' === $entryKey) {
                    /** @var Products_Model $products */
                    $products = model(Products_Model::class);
                    $products->updateMany(['thumbs_actualized' => 0]);
                    $log = fopen('public/items/thumbs_actualize_log.txt', 'w+');
                    fclose($log);
                }

                try {
                    $this->generateConfigCache();
                } catch (\Throwable $e) {
                    // Silently fail.
                }

                jsonResponse('The configuration entry has been successfully updated.', 'success');

            break;
            case 'regenerate_configs':
                try {
                    $this->generateConfigCache();
                } catch (\Throwable $e) {
                    jsonResponse(nl2br("Failed to regenerate configuration cache due to error: \n{$e->getMessage()}"));
                }

                jsonResponse('This method of config caching is deprecated. Please use the console commands instead.', 'warning');

            break;
        }
    }

    /**
     * Regenerates the configuration cache.
     *
     * @deprecated 2.27.0 Use console command instead
     */
    public function regenerate(): void
    {
        trigger_deprecation('app', '2.27', 'This method of config caching is deprecated. Please use the console commands instead.');
    }

    /**
     * Generates the config cache using console command.
     *
     * @throws ProcessFailedException if failed to execute command
     */
    private function generateConfigCache(): void
    {
        $process = new Process(['php', 'bin/console', 'config:cache']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}
