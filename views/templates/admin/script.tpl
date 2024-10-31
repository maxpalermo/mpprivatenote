<script type="text/javascript">
    const note_id_order = "{$id_order}";
    const note_id_customer = "{$id_customer}";
    const note_id_employee = "{$id_employee}";
    const note_admin_link = "{$admin_link}";
    const note_modal_list = "listCustomerNoteModal";
    const note_modal_add = "addCustomerNoteModal";
    const note_modal_table = "table-customer-note-list";

    function getCustomerMessages(showModal = false) {
        $.ajax({
            url: note_admin_link,
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'getCustomerMessages',
                id_employee: note_id_employee,
                id_customer: note_id_customer,
            },
            success: function(response) {
                var rows = $("#" + note_modal_table + " tbody");
                $(rows).html('');
                var msg = response.result;
                $(msg).each(function() {
                    $(rows).append(
                        $('<tr></tr>')
                        .append($('<td></td>').text(this.id_customer_messages))
                        .append($('<td></td>').text(this.firstname + ' ' + this.lastname))
                        .append($('<td></td>').text(this.date_add))
                        .append($('<td class="td-msg"></td>').text(this.message))
                        .append($('<td></td>').html(this.action))
                    );
                });
                if (showModal) {
                    $('#' + note_modal_list).modal('show');
                }
            },
            error: function(response) {
                console.log(response.responseText);
            }
        });
    }

    function saveCustomerMessage() {
        $.ajax({
            url: note_admin_link,
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'saveCustomerMessage',
                id_employee: note_id_employee,
                id_customer: note_id_customer,
                message: $('#privateNoteMessage').val()
            },
            success: function(response) {
                $.growl.notice({
                    title: '{l s='Nuovo messaggio' mod='mpprivatenotes'}',
                    message: '{l s='Messaggio inserito in archivio.' mod='mpprivatenotes'}'
                });
                $("#" + note_modal_add).modal('hide');
            },
            error: function(response) {
                console.log(response.responseText);
            }
        });
    }

    function deleteMessage(id) {
        $.ajax({
            url: note_admin_link,
            type: 'post',
            dataType: 'json',
            data: {
                ajax: true,
                action: 'deleteMessage',
                id_employee: note_id_employee,
                id_customer: note_id_customer,
                id_customer_messages: id
            },
            success: function(response) {
                $.growl.notice({
                    title: '{l s='Messaggio eliminato' mod='mpprivatenotes'}',
                    message: '{l s='Il messaggio è stato eliminato.' mod='mpprivatenotes'}'
                });
                getCustomerMessages();
            },
            error: function(response) {
                console.log(response.responseText);
            }
        });
    }

    function showModalPrivateNotes() {
        getCustomerMessages(true);
    }


    $(document).ready(function() {
        $(document).on("show.bs.modal", "#" + note_modal_add, function() {
            $("#" + note_modal_list).modal('hide');
        });

        $(document).on("shown.bs.modal", "#" + note_modal_add, function() {
            $('#privateNoteMessage').text('').focus();
        });

        $(document).on("click", "#btn-save-note", function() {
            if (!confirm("{l s='Save this note?' mod='mpprivatenotes'}"))
            return false;

            saveCustomerMessage();
        });

        $(document).on("click", ".delete-message", function() {
            if (!confirm("{l s='Eliminare questa nota?' mod='mpprivatenotes'}"))
            return false;

            deleteMessage($(this).data('id'));
        });
    });
</script>