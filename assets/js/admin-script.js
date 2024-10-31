jQuery(document).ready(function($) {
    var ajaxUrl = ajax_object.ajax_url;

    $('#setting_post').submit(function(event){
        event.preventDefault();
        var formData = $('#setting_post').serialize() + '&action=pacp_setting_post';
        $.ajax({
            type: 'POST',
            url: ajaxUrl,
            data: formData,
            success: function(response) {
               location.reload();
            },
            error: function(xhr, status, error) {
                console.error(xhr.responseText);
            }
        });
    });


    if ($('#pacp_listing_table').length) {
        var table = $('#pacp_listing_table').DataTable({
          "columnDefs": [
            { "orderable": false, "targets": 0 }
          ],
          "lengthMenu": [ [10, 25, 50, 100], [10, 25, 50, 100] ],
            "language": {
                "info": "1–10 of _TOTAL_",
                 
            }
        });
    }
 
    $('#select-all-bottom').on('change', function() {
        $('.bulk-checkbox').prop('checked', this.checked);
    });

   
    $('.bulk-checkbox').on('change', function() { 
        var allChecked = $('.bulk-checkbox:checked').length === $('.bulk-checkbox').length;
        $('#select-all-bottom').prop('checked', allChecked);
    });

    $(".post-listing-content .action_container  span:not(.edit) a").on("click", function(e) {
        e.preventDefault();
        var getclass =$(this).attr('class');
        var id =$(this).attr('data-id');

        if (getclass == 'trash') {
            var triger = 'trash';
        }else if(getclass == 'pacdduplicate'){
            var triger = 'duplicate';
        }else if(getclass == 'pacddeactivate'){
            var triger = 'activate';
        }else if(getclass == 'pacdactivate'){
            var triger = 'deactivate';
        }

        if (triger != '' && id != '') {

            var post_nonce = ajax_object.post_nonce;
            
            $.ajax({
                type: "post",
                url: ajaxUrl,
                data: {
                    action:'pacp_triger_post',
                    id: id,
                    triger:triger,
                    pacp_triger_post_nonce: post_nonce
                },
                beforeSend: function () {
                    $('.ajax-loader').css("visibility", "visible");
                },
                complete: function () {
                    $('.ajax-loader').css("visibility", "hidden");
                },
                success: function(response){
                    window.location.href = 'admin.php?page=portfolio-and-custom-posts';
                }
            }); 
        }
    });

 

    $(".taxonomy-content .action_container  span:not(.edit) a").on("click", function(e) {
        e.preventDefault();
        var getclass =$(this).attr('class');
        var id =$(this).attr('data-id');

        if (getclass == 'trash') {
            var triger = 'trash';
        }else if(getclass == 'pacdduplicate'){
            var triger = 'duplicate';
        }else if(getclass == 'pacddeactivate'){
            var triger = 'activate';
        }else if(getclass == 'pacdactivate'){
            var triger = 'deactivate';
        }


        var taxonomy_nonce = ajax_object.taxonomy_nonce;

        if (triger != '' && id != '') {
            $.ajax({
                type: "post",
                url: ajaxUrl,
                data: {
                    action:'pacp_triger_taxonomies',
                    id: id,
                    triger:triger,
                    pacp_triger_taxonomies_nonce: taxonomy_nonce
                },
                beforeSend: function () {
                    $('.ajax-loader').css("visibility", "visible");
                },
                complete: function () {
                    $('.ajax-loader').css("visibility", "hidden");
                },
                success: function(response){
                    window.location.href = 'admin.php?page=pacp-taxonomy-type';
                }
            }); 
        }
    });
});
