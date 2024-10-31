{**
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
 *}

{include file="module:mpprivatenotes/views/templates/admin/modal-panel.tpl"}

<div class="panel panel-sm" id="enhancedcustomernote">
    <div class="panel-heading">
        <i class="icon-eye-slash"></i>
        {l s='Private note' mod='mpcustomernotes'}
    </div>
    <div class="panel-body">
        <div style="overflow-y: auto; height: 12em;">
            <table class="table table-striped table-condensed" id="table-customer-message">
                <thead>
                    <tr>
                        <th>Id</th>
                        <th>Impiegato</th>
                        <th>Data</th>
                        <th>Messaggio</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
        </div>
        <hr>
        <div>
            <label>Inserisci messaggio</label>
            <br>
            <textarea id="text-customer-message" class="form-control" rows="2"></textarea>
            <br>
            <button type="button" class="btn btn-default pull-right" id="save-customer-message">
                <i class="icon icon-2x icon-save text-danger"></i>&nbsp;Salva
            </button>
            <button type="button" class="btn btn-default pull-left" id="get-customer-message" style="display: none;">
                <i class="process-icon-upload"></i>Importa
            </button>
        </div>
    </div>
</div>

{include file="module:mpprivatenotes/views/templates/admin/script.tpl"}