<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 */

use MpSoft\MpPrivateNotes\Models\ModelCustomerNote;

class AdminMpPrivateNotesController extends ModuleAdminController
{
    public $id_lang;
    public $id_shop;
    public $link;
    public $className;
    protected $messages;
    protected $local_path;

    public function __construct()
    {
        $this->translator = Context::getContext()->getTranslator();
        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->token = Tools::getValue('token', Tools::getAdminTokenLite($this->className));

        $this->lang = true;
        $this->table = 'customer';
        $this->identifier = 'id_customer';
        $this->className = 'ModelCustomerNote';

        $this->bulk_actions = [
            'delete_pdf' => [
                'text' => $this->trans('Elimina i messaggi selezionati'),
                'confirm' => $this->trans('Confermare la cancellazione dei messaggi selezionati?'),
                'icon' => 'icon-trash',
                'href' => $this->context->link->getAdminLink($this->controller_name, true) . '&action=delete',
            ],
        ];

        parent::__construct();

        $this->id_lang = (int) ContextCore::getContext()->language->id;
        $this->id_shop = (int) ContextCore::getContext()->shop->id;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $this->addJqueryUI('ui.dialog');
        $this->addJqueryUI('ui.progressbar');
        $this->addJqueryUI('ui.draggable');
        $this->addJqueryUI('ui.effect');
        $this->addJqueryUI('ui.effect-slide');
        $this->addJqueryUI('ui.effect-fold');
        $this->addJqueryUI('ui.progressbar');
    }

    public function response($data)
    {
        header('Content-Type: application/json');
        ob_clean();
        exit(json_encode($data));
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();

        $this->page_header_toolbar_btn['new'] = [
            'href' => 'javascript:$("#desc-product-new").click();',
            'desc' => $this->trans('Aggiungi un messaggio'),
            'icon' => 'process-icon-new',
        ];
    }

    public function initToolbar()
    {
        parent::initToolbar();
        $this->toolbar_btn['new'] = [
            'href' => 'javascript:$("#desc-product-new").click();',
            'desc' => $this->trans('Aggiungi un messaggio'),
            'icon' => 'process-icon-new',
        ];
    }

    public function initList()
    {
        $join_table = _DB_PREFIX_ . MpSizeChartModelAttachments::$definition['table'];
        $this->_join .= " LEFT JOIN `{$join_table}` m ON (m.id_product = a.id_product)";
        $this->_select = 'm.file_name, m.file_size, m.file_type, a.id_product as image';

        $this->fields_list = [
            'image' => [
                'title' => $this->trans('Immagine'),
                'align' => 'center',
                'orderby' => false,
                'search' => false,
                'callback' => 'displayImage',
                'type' => 'bool',
                'float' => true,
            ],
            'id_product' => [
                'title' => $this->trans('Id'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name' => [
                'title' => $this->trans('Nome'),
                'filter_key' => 'pl!name',
            ],
            'reference' => [
                'title' => $this->trans('Riferimento'),
                'filter_key' => 'p!reference',
            ],
            'price' => [
                'title' => $this->trans('Prezzo'),
                'type' => 'price',
                'currency' => true,
                'align' => 'right',
                'filter_key' => 'p!price',
                'class' => 'fixed-width-sm text-right',
            ],
            'active' => [
                'title' => $this->trans('Attivo'),
                'active' => 'status',
                'type' => 'bool',
                'align' => 'center',
                'class' => 'fixed-width-sm',
            ],
            'file_name' => [
                'title' => $this->trans('File'),
                'orderby' => false,
                'search' => false,
                'float' => true,
                'class' => 'text-center',
                'filter_key' => 'm!file_name',
                'callback' => 'displayPdf',
                'remove_onclick' => true,
            ],
            'file_size' => [
                'title' => $this->trans('Peso'),
                'orderby' => false,
                'search' => false,
                'class' => 'fixed-width-md text-right',
                'callback' => 'formatBytes',
            ],
            'file_type' => [
                'title' => $this->trans('Tipo'),
                'orderby' => false,
                'search' => false,
                'class' => 'fixed-width-md text-center',
            ],
        ];

        $this->actions = ['add', 'edit', 'delete'];
    }

    public function formatBytes($value)
    {
        if (!$value) {
            return '--';
        }

        return Tools::formatBytes((int) $value);
    }

    protected function initFormSearch()
    {
        $categories = Category::getCategories($this->id_lang, true, false);
        $selected_categories = Tools::getValue('categories', []);
        $tree = new HelperTreeCategories('categories-tree');
        $tree->setUseCheckBox(true)
            ->setUseSearch(true)
            ->setSelectedCategories($selected_categories);

        $this->fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->trans('Pannello di ricerca'),
                    'icon' => 'icon-search',
                ],
                'input' => [
                    [
                        'type' => 'categories',
                        'label' => $this->trans('Seleziona le categorie'),
                        'name' => 'categories',
                        'tree' => [
                            'id' => 'categories-tree',
                            'selected_categories' => $selected_categories,
                            'use_search' => true,
                            'use_checkbox' => true,
                            'input_name' => 'categories[]',
                        ],
                        'required' => false,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Seleziona i Produttori'),
                        'name' => 'manufacturers',
                        'class' => 'chosen',
                        'options' => [
                            'query' => array_merge([['id_manufacturer' => 0, 'name' => '--']], Manufacturer::getManufacturers()),
                            'id' => 'id_manufacturer',
                            'name' => 'name',
                        ],
                        'multiple' => true,
                    ],
                    [
                        'type' => 'select',
                        'label' => $this->trans('Seleziona i Fornitori'),
                        'name' => 'suppliers',
                        'class' => 'chosen',
                        'options' => [
                            'query' => array_merge([['id_supplier' => 0, 'name' => '--']], Supplier::getSuppliers()),
                            'id' => 'id_supplier',
                            'name' => 'name',
                        ],
                        'multiple' => true,
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->trans('Nome Prodotto'),
                        'name' => 'product_name',
                    ],
                    [
                        'type' => 'html',
                        'label' => $this->trans('cerca in'),
                        'name' => 'chk_search_in',
                        'html_content' => $this->getButtonsSearchIn(),
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Solo i prodotti con allegati'),
                        'name' => 'switch_only_attachments',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'switch_only_attachments_yes',
                                'value' => 1,
                                'label' => $this->trans('SI'),
                            ],
                            [
                                'id' => 'switch_only_attachments_no',
                                'value' => 0,
                                'label' => $this->trans('NO'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'switch',
                        'label' => $this->trans('Solo i prodotti senza allegati'),
                        'name' => 'switch_no_attachments',
                        'is_bool' => true,
                        'values' => [
                            [
                                'id' => 'switch_no_attachments_yes',
                                'value' => 1,
                                'label' => $this->trans('SI'),
                            ],
                            [
                                'id' => 'switch_no_attachments_no',
                                'value' => 0,
                                'label' => $this->trans('NO'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'hidden',
                        'name' => 'action',
                    ],
                ],
                'submit' => [
                    'title' => $this->trans('Cerca'),
                    'class' => 'btn btn-default pull-right',
                    'icon' => 'process-icon-search',
                ],
                'buttons' => [
                    [
                        'title' => $this->trans('Reset'),
                        'class' => 'btn btn-default pull-left',
                        'name' => 'submitSearch',
                        'icon' => 'process-icon-cancel',
                        'href' => $this->context->link->getAdminLink($this->controller_name, true, [], ['action' => 'resetSearchFilters']),
                    ],
                ],
            ],
        ];

        $allow_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');

        $helper = new HelperForm();
        $helper->module = $this->module;
        $helper->name_controller = $this->className;
        $helper->token = Tools::getAdminTokenLite($this->controller_name);
        $helper->currentIndex = $this->context->link->getAdminLink($this->controller_name);
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = $allow_form_lang ? $allow_form_lang : 0;
        $helper->title = $this->module->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submit' . $this->className;
        $helper->fields_value = $this->getConfigFieldsValuesSearch();

        return $helper->generateForm([$this->fields_form]);
    }

    protected function getConfigFieldsValuesSearch()
    {
        $fields = [];
        $fields['categories'] = Tools::getValue('categories', json_decode(Configuration::get('MPSIZECHART_CATEGORIES'), true));
        $fields['manufacturers[]'] = Tools::getValue('manufacturers', json_decode(Configuration::get('MPSIZECHART_MANUFACTURERS'), true));
        $fields['suppliers[]'] = Tools::getValue('suppliers', json_decode(Configuration::get('MPSIZECHART_SUPPLIERS'), true));
        $fields['product_name'] = Tools::getValue('product_name', Configuration::get('MPSIZECHART_PRODUCT_NAME'));
        $fields['chk_search_in'] = Tools::getValue('chk_search_in', json_decode(Configuration::get('MPSIZECHART_CHK_SEARCH_IN'), true));
        $fields['switch_only_attachments'] = (int) Tools::getValue('switch_only_attachments', (int) Configuration::get('MPSIZECHART_SWITCH_ONLY_ATTACHMENTS'));
        $fields['switch_no_attachments'] = (int) Tools::getValue('switch_no_attachments', (int) Configuration::get('MPSIZECHART_SWITCH_NO_ATTACHMENTS'));

        $fields['action'] = 'submitSearch';

        return $fields;
    }

    private function initScript()
    {
        $path = $this->module->getLocalPath() . 'views/templates/admin/script.tpl';
        $tpl = $this->context->smarty->createTemplate($path, $this->context->smarty);
        $tpl->assign(
            [
                'ajax_url' => $this->context->link->getAdminLink($this->controller_name),
                // $this->context->link->getModuleLink($this->module->name, 'ajaxDispatcher'),
                'token' => Tools::getAdminTokenLite($this->className),
                'attachments' => MpSizeChartGetAttachment::getAttachmentList(),
            ]
        );

        return $tpl->fetch();
    }

    public function ajaxProcessSaveCustomerMessage()
    {
        $id_customer = (int) Tools::getValue('id_customer');
        $id_employee = (int) Tools::getValue('id_employee');
        $message = Tools::getValue('message');

        $customerMessage = new ModelCustomerNote();
        $customerMessage->id_customer = $id_customer;
        $customerMessage->id_employee = $id_employee;
        $customerMessage->message = $message;
        $customerMessage->date_add = date('Y-m-d H:i:s');

        try {
            $res = $customerMessage->add();
            if (!$res) {
                $error = Db::getInstance()->getMsgError();
            }
        } catch (\Throwable $th) {
            $res = false;
            $error = $th->getMessage();
        }

        if ($res) {
            $this->response(['result' => 1]);
        } else {
            $this->response(['result' => 0, 'error' => $error]);
        }
    }

    public function ajaxProcessGetCustomerMessages()
    {
        $id_customer = (int) Tools::getValue('id_customer');
        $show_deleted = (int) Tools::getValue('show_deleted');
        $messages = ModelCustomerNote::getCustomerMessages($id_customer, $show_deleted);

        $this->response(['result' => $messages]);
    }

    public function ajaxProcessDeleteMessage()
    {
        $id = (int) Tools::getValue('id_customer_messages');
        $id_employee = (int) Tools::getValue('id_employee');
        if ($id) {
            $model = new ModelCustomerNote($id);
            if (!Validate::isLoadedObject($model)) {
                $this->response(['result' => 0, 'error' => $this->trans('Errore: messaggio non trovato.')]);
            }

            $model->deleted = 1;
            $model->deleted_by = $id_employee;
            $model->deleted_at = date('Y-m-d H:i:s');

            try {
                $res = $model->update();
            } catch (\Throwable $th) {
                $res = false;
                $error = $th->getMessage();
            }

            if ($res) {
                $this->response(['result' => 1]);
            } else {
                $this->response(['result' => 0, 'error' => $error]);
            }
        }

        $this->response(['result' => 0, 'error' => $this->trans('Errore: id non valido.')]);
    }
}
