M.ext_cdn = {
    Y: null,
    transaction: [],
    init: function(Y) {
        $("#id_s_cdn_enablecriteriamimetype").click(function() {
            $("#id_s_cdn_enablecriteriaextension").val('').prop('disabled', true);
            $("#id_s_cdn_enablecriteriaonlyscorm").attr('checked', false).prop('disabled', true);
            $("#id_s_cdn_enablecriteriaallfiles").attr('checked', false).prop('disabled', true);
        })
        $("#id_s_cdn_enablecriteriasize").click(function() {
            $("#id_s_cdn_enablecriteriaallfiles").attr('checked', false).prop('disabled', true);
        })
        $("#id_s_cdn_enablecriteriaextension").click(function() {
            $("#id_s_cdn_enablecriteriamimetype").val('').prop('disabled', true);
            $("#id_s_cdn_enablecriteriaonlyscorm").attr('checked', false).prop('disabled', true);
            $("#id_s_cdn_enablecriteriaallfiles").attr('checked', false).prop('disabled', true);
        })
        $("#id_s_cdn_enablecriteriaonlyscorm").click(function() {
            if ($('#id_s_cdn_enablecriteriaonlyscorm').prop("checked") == true) {
                $("#id_s_cdn_enablecriteriamimetype").val('').prop('disabled', true);
                $("#id_s_cdn_enablecriteriasize").val('');
                $("#id_s_cdn_enablecriteriaextension").val('').prop('disabled', true);
                $("#id_s_cdn_enablecriteriaallfiles").attr('checked', false).prop('disabled', true);
            } else {
                $("#id_s_cdn_enablecriteriamimetype").val('').prop('disabled', false);
                $("#id_s_cdn_enablecriteriasize").val('').prop('disabled', false);
                $("#id_s_cdn_enablecriteriaextension").val('').prop('disabled', false);
                $("#id_s_cdn_enablecriteriaallfiles").attr('checked', false).prop('disabled', false);
            }
        })
        $("#id_s_cdn_enablecriteriaallfiles").click(function() {
            if ($('#id_s_cdn_enablecriteriaallfiles').prop("checked") == true) {
                $("#id_s_cdn_enablecriteriamimetype").val('').prop('disabled', true);
                $("#id_s_cdn_enablecriteriasize").val('').prop('disabled', true);
                $("#id_s_cdn_enablecriteriaextension").val('').prop('disabled', true);
                $("#id_s_cdn_enablecriteriaonlyscorm").attr('checked', false).prop('disabled', true);
            } else {
                $("#id_s_cdn_enablecriteriamimetype").val('').prop('disabled', false);
                $("#id_s_cdn_enablecriteriasize").val('').prop('disabled', false);
                $("#id_s_cdn_enablecriteriaextension").val('').prop('disabled', false);
                $("#id_s_cdn_enablecriteriaonlyscorm").attr('checked', false).prop('disabled', false);
            }
        })

        $("#adminsettings").submit(function(e) {
            e.preventDefault();
            e.returnValue = false;
            // Updating operators via AJAX.
             $.ajax({
                 type: "POST",
                 url: M.cfg.wwwroot + '/ext/cdn/sam.php?id=5',
                 data: ({
                     'id_s_cdn_enablecriteriaonlyscorm' : $('#id_s_cdn_enablecriteriaonlyscorm').val(),
                 }),
                 success: function(o) {
                     // If success, update operators description in the client side.
                     alert(o);
                     
                    // this.submit();
                       
                 }
             }); 
            $("#id_s_cdn_enablecriteriasize").prop('disabled', false);
            $("#id_s_cdn_enablecriteriaextension").prop('disabled', false);
            $("#id_s_cdn_enablecriteriaonlyscorm").prop('disabled', false);
            $("#id_s_cdn_enablecriteriaallfiles").prop('disabled', false);
            $("#id_s_cdn_enablecriteriamimetype").prop('disabled', false);
        })
        this.Y = Y;
    },
}