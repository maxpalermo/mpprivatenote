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

<div class="modal fade" id="listCustomerNoteModal" tabindex="-1" role="dialog" aria-labelledby="listCustomerNoteModalLabel" aria-hidden="true" data-keyboard="false">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title pull-left" id="listCustomerNoteModalLabel">
                    <i class="icon icon-note"></i>
                    Visualizza Messaggi per il Cliente
                </h3>
                <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" style="min-height: 15rem;">
                <table class="table table-striped" id="table-customer-note-list">
                    <thead>
                        <tr>
                            <th scope="col">Data</th>
                            <th scope="col">Messaggio</th>
                            <th scope="col">Impiegato</th>
                            {if (!empty($customerNotes) && isset($customerNotes[0].action))}
                                <th scope="col">Azioni</th>
                            {/if}
                        </tr>
                    </thead>
                    <tbody>
                        {if empty($customerNotes)}
                            <tr>
                                <td colspan="3" class="text-center">
                                    .<div class="alert alert-warning" role="alert">
                                        Nessun messaggio trovato
                                    </div>
                                </td>
                            </tr>
                        {else}
                            {foreach from=$customerNotes item=note}
                                <tr>
                                    <td>{$note.date_add}</td>
                                    <td>{$note.message}</td>
                                    <td>{if $note.firstname}{$note.firstname}{/if} {if $note.lastname}{$note.lastname}{/if}</td>
                                    {if (!empty($customerNotes) && isset($customerNotes[0].action))}
                                        <th scope="col">{$note.action}</th>
                                    {/if}
                                </tr>
                            {/foreach}
                        {/if}
                    </tbody>
                </table>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn btn-secondary justify-content-end" data-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary justify-content-start" data-toggle="modal" data-target="#addCustomerNoteModal">Nuovo Messaggio</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="addCustomerNoteModal" tabindex="-1" role="dialog" aria-labelledby="addCustomerNoteModalLabel" aria-hidden="true" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title pull-left" id="addCustomerNoteModalLabel">
                    <i class="icon icon-message"></i>
                    Inserisci Messaggio Privato
                </h3>
                <button type="button" class="close pull-right" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="privateNoteForm">
                    <div class="form-group">
                        <label for="privateNoteMessage">Messaggio</label>
                        <textarea class="form-control" id="privateNoteMessage" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer d-flex">
                <button type="button" class="btn btn-secondary justify-content-end" data-dismiss="modal">Chiudi</button>
                <button type="button" class="btn btn-primary justify-content-start" id="btn-save-note">Salva</button>
            </div>
        </div>
    </div>
</div>

{include file="module:mpprivatenotes/views/templates/admin/script.tpl"}