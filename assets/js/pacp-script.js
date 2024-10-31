jQuery(document).ready(function($) {
    var ajaxUrl = ajax_object.ajax_url;


    var nonce = ajax_object.load_more_nonce;
    var ajaxUrl = ajax_object.ajax_url;
    var getPostType = ajax_object.get_post_type;
    var totalCount = ajax_object.totalCount;
    var perPage = ajax_object.per_page;
    var currentPostType = ajax_object.current_post_type;

    $('.toggle-items:nth-child(1) .toggle-header').addClass('show');
    $('.toggle-items:nth-child(1) .toggle-header + .toggle-collapse').addClass('show');
    $('.toggle-items > .toggle-header').click(function() {
        $(this).toggleClass('show');
        $(this).next().toggleClass('show');
    });

    function htmlContent(data){
        var content = $('.pacp_response > .container-fluid > .row');
         
        $.each(data, function(index, item) {
            var title = item.title;
            var url = item.url;
            var img = item.img;
            var excerpt = item.excerpt;
            content.append(`
                <div class="col-lg-4 col-md-6 col-12 pacp_holder">
                    <div class="pacp_head">
                        <span class="title_head">`+title+`</span>
                    </div>
                    <div class="pacp_container">
                        <a href="`+url+`" class="thumbnail-image">
                            <img src="`+img+`">
                        </a>
                        <div class="the_content_data">
                            <a href="`+url+`" class="pacp_title">`+title+`</a>
                            <div class="the_excerpt"><p>`+excerpt+`</p>
                            </div>
                        </div>
                    </div>
                    <div class="pacp_footer">
                        <a class="pacp_read-more" href="`+url+`">Read More →</a>
                    </div>
                </div>
            `);
        });
    }

    var urlParams = new URLSearchParams(window.location.search);
    var size = 0;
    urlParams.forEach(function(value, key) {
        size += 1;
    });


    if (size > 0) {
        var filter = $('#pacp_filter');
        $.ajax({
            url: ajaxUrl,
            data: filter.serialize(),
            type: filter.attr('method'),
            beforeSend: function(xhr) {
                $('#response').html('<div class="pageloadingElement"><div class="lds-roller"></div></div>'); 
            },
            success: function(data) {
                var jsonData = JSON.parse(data);
                var data =jsonData['data'];
                var total =jsonData['total'];
                var content = $('.pacp_response > .container-fluid > .row');

                var get_post_type = $('.content-area h2').text();
                content.empty();

                content.append(`
                    <div class="col-12 pacp_holder_header">
                        <div class="projectCount">
                            <span class="count-icon">
                                <img src="'../images/total-projects.png"/>
                            </span>
                            <div class="count-total">
                            <span id="totalCount">`+total+`</span>
                                <b>Total `+get_post_type+`</b>
                            </div>
                        </div>
                    </div>`);

                htmlContent(data);
            }
        });
    }

    jQuery(".toggle_btn").click(function(){
        jQuery(this).parents('.filterInputContainer').toggleClass('open');
    });

    $(".toggle-more-btn").click(function(){
        $(this).parents('.toggle-collapse').toggleClass("show-more");
        if ($(this).parents('.toggle-collapse').hasClass("show-more")) {
            $(this).text("- Show less");
        } else {
            $(this).text("+ Show more");
        }
    });

    function copyToClipboard(text) {
        var tempInput = $('<input>');
        $('body').append(tempInput);
        tempInput.val(text).select();
        document.execCommand('copy');
        tempInput.remove();
    }

    $(document).on("click",".copy_btn", function(){
        var dataId = $(this).attr('data-id');
        copyToClipboard(dataId);
        console.log('Copied: ' + dataId);
    });

    $(document).on("click",".filter_button span", function(){
        $(this).toggleClass('open');
        $('#pacp_filter').toggle('show');
    });


    $(document).on('change', '.filterContainer .filterInputContainer input', function(e) {
        if ($('.filterContainer .filterInputContainer input:checked').length < 1) {
            $('.load_more').append('<button class="load_btn">Load More</button>');
        }else{
            $('.load_more').empty();
        }
        if ($(this).is(':checked')) {

            var copy = currentPostType+'?'+$(this).attr('data-id')+'='+$(this).attr('value');
            $('.copy_btn').remove();
            $(this).parent('.filterInputContainer').before('<span class="copy_btn" data-id="'+copy+'">Copy</span>')
        }else{
            $('.copy_btn').remove();
        }
       
        var filter = $('#pacp_filter');

        $.ajax({
            url: ajaxUrl,
            data: filter.serialize(),
            type: filter.attr('method'),
            beforeSend: function(xhr) {
                $('#response').html('<div class="pageloadingElement"><div class="lds-roller"></div></div>'); 
            },
            success: function(data) {
                var jsonData = JSON.parse(data);
                var data =jsonData['data'];
                var total =jsonData['total'];

               
                if (jsonData['total'] != '' && jsonData['total'] > 12) {
                    $('.load_more').append('<button class="load_btn" data-id="12">Load More</button>');
                }

                var content = $('.pacp_response > .container-fluid > .row');
                content.empty();

                content.append(`
                    <div class="col-12 pacp_holder_header">
                        <div class="projectCount">
                            <span class="count-icon">
                                <img src="../images/total-projects.png"/>
                            </span>
                            <div class="count-total">
                            <span id="totalCount">`+total+`</span>
                                <b>`+getPostType+`</b>
                            </div>
                        </div>
                    </div>`);

                htmlContent(data);
            }
        });
    });


    $(document).on('click', '.load_btn', function() {
        $('.load_more').empty();
        var data_id = $(this).attr('data-id');
        
        $.ajax({
            url: ajaxUrl,
            type: 'POST',
            data: {
                action: 'pacp_pacpload_more',
                data_id: data_id,
                post_type: currentPostType,
                pacp_load_more_nonce: nonce
            },
            beforeSend: function(xhr) {
                $('#response').html('<div class="pageloadingElement"><div class="lds-roller"></div></div>');
            },
            success: function(data) {

                var jsonData = JSON.parse(data);

                var data =jsonData['data'];
                var total =jsonData['total'];
                var count =jsonData['count'];

                var content = $('.pacp_response > .container-fluid > .row');

                var per_page = perPage;

                var new_per_page = parseInt(per_page) + parseInt(count);

                if (totalCount > count) {
                    $('.load_more').append(`<button class="load_btn" data-id="`+new_per_page+`">Load More</button>`);
                }
                content.empty();
                content.append(`
                    <div class="col-12 pacp_holder_header">
                        <div class="projectCount">
                            <span class="count-icon">
                                <img src="../images/total-projects.png"/>
                            </span>
                            <div class="count-total">
                            <span id="totalCount">`+total+`</span>
                                <b>Total `+getPostType+`</b>
                            </div>
                        </div>
                    </div>`);
                htmlContent(data);
            }
        });
    })
});