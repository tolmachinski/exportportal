<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [15.12.2021]
 * Controller Refactoring
 */
class Country_Controller extends TinyMVC_Controller
{
    public function administration(){
        checkAdmin('country_administration');

        /** @var Continents_Model $continentsModel */
        $continentsModel = model(Continents_Model::class);

        views(
            [
                'admin/header_view',
                'admin/countries/index_view',
                'admin/footer_view'
            ],
            [
                'continents'    => $continentsModel->findAll(),
                'title'         => 'The list of countries.',
            ]
        );
    }

    public function ajax_dt_countries(){
        checkIsAjax();
        checkPermisionAjaxDT('country_administration');

        $request = request()->request;

        /** @var Countries_Model $portCountryModel */
        $portCountryModel = model(Countries_Model::class);

        $conditions = dtConditions(
            $request->all(),
            [
                ['as' => 'isFocusCountry',      'key' => 'focus_country',       'type' => 'int'],
                ['as' => 'continentId',         'key' => 'continent',           'type' => 'int'],
                ['as' => 'countryCode',         'key' => 'code',                'type' => 'cleaninput|trim'],
                ['as' => 'country',             'key' => 'country',             'type' => 'cleaninput|trim'],
                ['as' => 'hasSpecialPosition',  'key' => 'special_position',    'type' => 'bool'],
            ]
        );

        $order = array_column(
            dtOrdering(
                $request->all(),
                [
                    'dt_country_position'   => 'position_on_select',
                    'dt_country_name'       => 'country',
                ]
            ),
            'direction',
            'column',
        );

        $joins = [];
        if (!empty($conditions['countryCode'])) {
            $joins[] = 'countryCodes';
        }

        $countries = $portCountryModel->findAllBy([
            'conditions' => $conditions,
            'joins' => $joins,
            'with'  => ['countryCode', 'continent'],
            'order' => $order,
            'limit' => abs($request->getInt('iDisplayLength')) ?: null,
            'skip'  => abs($request->getInt('iDisplayStart')) ?: null,
        ]);

        $countCountries = $portCountryModel->countBy([
            'conditions' => $conditions,
            'joins' => $joins,
        ]);

        $output = [
            'iTotalDisplayRecords'  => $countCountries,
            'iTotalRecords'         => $countCountries,
			'aaData'                => [],
            'sEcho'                 => $request->getInt('sEcho'),
        ];

        if (empty($countries)) {
            jsonResponse('', 'success', $output);
        }

        foreach ($countries as $country) {
            $dt_translations = '';
            $add_translation_btn = '<a href="' . __SITE_URL . 'country/popup_forms/add_translation/' . $country['id'] . '" data-title="Add translation" class="fancyboxValidateModalDT fancybox.ajax"><i class="ep-icon ep-icon_globe-circle"></i></a>';
            $edit_country_btn = '<a class="ep-icon ep-icon_pencil fancybox.ajax fancyboxValidateModalDT tooltipstered" href="' . __SITE_URL . 'country/popup_forms/edit_country/' . $country['id'] . '" data-title="Edit country"></a>';

            if (!empty($country['translations_data'])) {
                $translations_data = json_decode($country['translations_data'], true);

                foreach ($translations_data as $language_abr => $country_translation) {
                    $dt_translations .= '<a href="' . __SITE_URL . 'country/popup_forms/edit_translation/' . $country['id'] . '/lang/' . $language_abr . '" class="btn btn-xs btn-primary mb-5 mr-3 fancyboxValidateModalDT fancybox.ajax" data-title="Edit translation" title="' . cleanOutput($country_translation) . '">' . strtoupper($language_abr) . '</a>';
                }
            }

            $output['aaData'][] = [
				'dt_country_continent'  => $country['continent']['name_continent'],
                'dt_country_is_focus'   => $country['is_focus_country'] ? '<i class="ep-icon ep-icon_ok txt-green"></i>' : '<i class="ep-icon ep-icon_remove txt-orange"></i>',
                'dt_country_position'   => empty($country['position_on_select']) ? 'Only alphabetical' : '<span class="label label-success">' . $country['position_on_select'] . '</span>',
                'dt_country_actions'    => $add_translation_btn . $edit_country_btn,
				'dt_country_code'       => empty($country['country_code']) ? '' : implode('<br>', array_column($country['country_code']->toArray(), 'ccode')),
                'dt_translations'       => $dt_translations,
				'dt_country_name'       => $country['country'],
                'dt_country_id'         => $country['id'],
			];
        }

        jsonResponse('', 'success', $output);
    }

    public function ajax_operations(){
        checkIsAjax();
        checkPermisionAjax('country_administration');

        switch (uri()->segment(3)) {
            case 'edit_country':
                $validator_rules = array(
					array(
						'field' => 'country_name',
						'label' => 'Country',
						'rules' => array('trim', 'required' => '', 'max_len[255]' => '')
					),
					array(
						'field' => 'country_continent',
						'label' => 'Continent',
						'rules' => array('required' => '', 'integer' => '')
                    ),
					array(
						'field' => 'abr',
						'label' => 'Abr',
						'rules' => array('required' => '', 'exact_len[2]' => '')
                    ),
					array(
						'field' => 'abr3',
						'label' => 'Abr3',
						'rules' => array('required' => '', 'exact_len[3]' => '')
                    ),
					array(
						'field' => 'country_latitude',
						'label' => 'Country latitude',
						'rules' => array('required' => '', 'max_len[20]' => '', 'float' => '')
                    ),
					array(
						'field' => 'country_longitude',
						'label' => 'Country longitude',
						'rules' => array('required' => '', 'max_len[20]' => '', 'float' => '')
                    ),
					array(
						'field' => 'position',
						'label' => 'Position in select',
						'rules' => array('integer' => '')
                    ),
				);

				$this->validator->set_rules($validator_rules);

				if (!$this->validator->validate()) {
					jsonResponse($this->validator->get_array_errors());
                }

                $request = request()->request;

                /** @var Countries_Model $portCountryModel */
                $portCountryModel = model(Countries_Model::class);

                if (empty($countryId = (int) uri()->segment(4)) || empty($country = $portCountryModel->findOne($countryId))) {
                    jsonResponse('Country ID is wrong');
                }

                /** @var Continents_Model $continentsModel */
                $continentsModel = model(Continents_Model::class);

                if (empty($continentId = $request->getInt('country_continent')) || empty($continent = $continentsModel->findOne($continentId))) {
                    jsonResponse('Continent ID is wrong');
                }

                $countryName = $request->get('country_name');

                $countryUpdates = array(
                    'country_ascii_name'    => (Transliterator::create('Any-Latin; Latin-ASCII;'))->transliterate($countryName),
                    'position_on_select'    => $request->get('position') ?: null,
                    'country_longitude'     => $request->get('country_longitude'),
                    'country_latitude'      => $request->get('country_latitude'),
                    'is_focus_country'      => (int) filter_var($request->get('is_focus'), FILTER_VALIDATE_BOOLEAN),
                    'country_alias'         => strForURL($countryName),
                    'country_name'          => $countryName,
                    'id_continent'          => $continentId,
                    'country'               => $countryName,
                    'abr3'                  => $request->get('abr3'),
                    'abr'                   => $request->get('abr'),
                );

                if (!$portCountryModel->updateOne($countryId, $countryUpdates, ['continent' => $continent])) {
                    jsonResponse('Some errors occurred while updating the country.');
                }

                jsonResponse('Coutry has been successfully edited.', 'success');
            break;
            case 'add_country':
            break;
            case 'edit_translation':
                $validator_rules = array(
					array(
						'field' => 'translation',
						'label' => 'Translated country name',
						'rules' => array('trim', 'required' => '', 'max_len[255]' => '')
					),
				);

				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
                }

                $country_id = (int) $_POST['country_id'];
                if (empty($country = model(Country_Model::class)->get_country($country_id))) {
                    jsonResponse('Country ID is wrong');
                }

                $lang_iso2 = $_POST['language_iso2'];

                $translations_data = empty($country['translations_data']) ? array() : json_decode($country['translations_data'], true);
                if (!isset($translations_data[$lang_iso2])) {
                    jsonResponse('Language key is wrong.');
                }

                $translations_data[$lang_iso2] = $_POST['translation'];

                $country_updates = array('translations_data' => json_encode($translations_data));
                if (!model(Country_Model::class)->update_country($country_id, $country_updates)) {
                    jsonResponse('Some errors occurred while updating the country translation.');
                }

                jsonResponse('Translated coutry name has been successfully edited.', 'success');
            break;
            case 'add_translation':
                $validator_rules = array(
					array(
						'field' => 'translation',
						'label' => 'Translated country name',
						'rules' => array('trim', 'required' => '', 'max_len[255]' => '')
					),
				);

				$this->validator->set_rules($validator_rules);

				if(!$this->validator->validate()){
					jsonResponse($this->validator->get_array_errors());
                }

                $country_id = (int) $_POST['country_id'];
                if (empty($country = model(Country_Model::class)->get_country($country_id))) {
                    jsonResponse('Country ID is wrong');
                }

                $language_id = (int) $_POST['language_id'];
                if (empty($language = model(Translations_Model::class)->get_language($language_id))) {
                    jsonResponse('Sorry, the translation language doesn\'t found. Please refresh page and try again.');
                }

                $translations_data = empty($country['translations_data']) ? array() : json_decode($country['translations_data'], true);

                if (isset($translations_data[$language['lang_iso2']])) {
                    jsonResponse('Translation in this language already exist');
                }

                $translations_data[$language['lang_iso2']] = $_POST['translation'];

                $country_updates = array('translations_data' => json_encode($translations_data));
                if (!model(Country_Model::class)->update_country($country_id, $country_updates)) {
                    jsonResponse('Some errors occurred while updating the country translation.');
                }

                jsonResponse('Translated coutry name has been successfully added in ' . $language['lang_name'] . '.', 'success');
            break;
            default:
                jsonResponse('Requested action is wrong.');
            break;
        }
    }

    public function popup_forms(){
        checkIsAjax();
        checkPermisionAjax('country_administration');

        $action = uri()->segment(3);

        switch ($action) {
            case 'edit_country':
                $country_id = (int) uri()->segment(4);
                if (empty($country = model(Country_Model::class)->get_country($country_id))) {
                    messageInModal('Country ID is wrong');
                }

                $continents = model(Country_Model::class)->get_continents();

                $data = array(
                    'action'            => __SITE_URL . 'country/ajax_operations/edit_country/' . $country_id,
                    'continents'        => $continents,
                    'country'           => $country,
                );

                views(array('admin/countries/form_view'), $data);

            break;
            case 'add_country':
            break;
            case 'edit_translation':
                $country_id = (int) uri()->segment(4);
                if (empty($country = model(Country_Model::class)->get_country($country_id))) {
                    messageInModal('Country ID is wrong.');
                }

                $lang_key = uri()->segment(6);

                $translations_data = empty($country['translations_data']) ? array() : json_decode($country['translations_data'], true);
                if (!isset($translations_data[$lang_key])) {
                    messageInModal('Language key is wrong.');
                }

                $language = model(Translations_Model::class)->get_language_by_iso2($lang_key);
                if (empty($language)) {
                    messageInModal('Sorry, the translation language doesn\'t found. Please refresh page and try again.');
                }

                $data = array(
                    'current_translation'   => $translations_data[$lang_key],
                    'en_translation'        => $translations_data['en'] ?? $country['country'],
                    'language_key'          => $lang_key,
                    'country_id'            => $country_id,
                    'language'              => $language['lang_name'],
                    'action'                => __SITE_URL . 'country/ajax_operations/edit_translation',
                );

                views(array('admin/countries/edit_translation_view'), $data);
            break;
            case 'add_translation':
                $country_id = (int) uri()->segment(4);
                if (empty($country = model(Country_Model::class)->get_country($country_id))) {
                    messageInModal('Country ID is wrong.');
                }

                $translations_data = empty($country['translations_data']) ? array() : json_decode($country['translations_data'], true);

                $data = array(
                    'translated_languages'  => array_keys($translations_data),
                    'en_translation'        => $translations_data['en'] ?? $country['country'],
                    'all_languages'         => model(Translations_Model::class)->get_languages(),
                    'country_id'            => $country_id,
                    'action'                => __SITE_URL . 'country/ajax_operations/add_translation',
                );

                views(array('admin/countries/add_translation_view'), $data);

            break;
            default:
                messageInModal('Requested action is wrong.');
            break;
        }
    }
}

/* End of file country.php */
/* Location: /tinymvc/myapp/controllers/country.php */
