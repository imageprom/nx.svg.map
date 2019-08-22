(function(){
    $(function(){

        var s_width = 700;

        function nxLabelsPosition() {

            nxScreen = $(window).width();

            $("#nxMapService .label").each(function() {
                data = $(this).data();
                if(nxScreen >= s_width) $(this).css({'top': data.top, 'left': data.left});
                else $(this).css({'top': 'auto', 'left': 'auto'});
            });
        }


        function nxVisible(id, option) {

            var status = parseInt($('#NxMapCountryLayer #'+id).data('active'));

            if(option) {
                if(!status) {
                    $('#nxMapService .label[data-for="' + id + '"]').addClass('nx-svg-map-dot_active').data('active', 1);
                    $('#NxMapCountryLayer #'+id).addClass('g-hover').data('active', 1);
                }
            }
            else {
                if(status) {
                    $('#nxMapService .label[data-for="' + id + '"]').removeClass('nx-svg-map-dot_active').data('active', 0);
                    $('#NxMapCountryLayer #'+id).removeClass('g-hover').data('active', 0);
                }
            }
        }

        function nxLinearGradient(id, index, data) {
            var res = '<linearGradient id="'+ id + '_' + index +'" ';

            $.each(data, function(code, value){
                if(code != 'offsets') {
                    res += code + '="' + value +'" ';
                }
            });

            if(index > 0) {
                res += 'xlink:href="#' + id + '_0" ';
            }

            res += '>';

            if(data.offsets) {
                $.each(data.offsets, function(i, value){
                    res += '<stop offset="' + i + '" stop-color="' + value.stopcolor + '" />';
                });
            }

            res += '</linearGradient>';

            return res;
        }

        function nxSvgObject(cls, data) {

            var res = '<' + data.type +' class="' + cls + '" ';

            $.each(data, function(code, value){
                if(code != 'type') {
                    res += code + '="' + value +'" ';
                }
            });

            res += ' />';

            return res;
        }

        function nxSvgMap (target) {

            $.ajax({
                url: window.location.pathname + '?nx_ajax_map_action=Y',
                type: 'GET',
                processData: false,
                contentType: false,
                timeout: 50000,
                beforeSend: function () {
                    //element.addClass('load');
                }
            }).done(function (data) {

                var svgObj = '';

                if(data.SVG) {
                    var SVG = data.SVG;

                    svgObj = '<svg id="nxSvgMap" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="' + SVG.viewBox + '">';
                    svgObj += '<defs>';

                    if(SVG.bg.gradient) {

                        if(SVG.bg.class) {
                            svgObj += '<style>';
                            $.each(SVG.bg.gradient,function(i, value){
                                svgObj += '	.' + SVG.bg.class + '-' + i + ' {fill: url(#' + SVG.bg.id + '_' + i + ');} ';
                            });

                            svgObj += '</style>';
                        }

                        $.each(SVG.bg.gradient,function(i, value){
                            svgObj += nxLinearGradient( SVG.bg.id, i, value);
                        });

                    }

                    svgObj += '</defs>';

                    if(SVG.bg.items) {
                        svgObj += '<g id="NxMapBgLayer" class="nx-svg-map-bg-layer"><g id="NxMapBg" class="nx-svg-map-bg">';
                        $.each(SVG.bg.items, function(i, value){
                            svgObj += nxSvgObject( SVG.bg.class + '-' + i, value);
                        });
                        svgObj += '</g></g>';
                    }

                    if(SVG.countries.items) {
                        svgObj += '<g id="NxMapCountryLayer" class="nx-svg-map-country-layer">';

                        $.each(SVG.countries.items, function(i, country){
                            var dot, gClass = '';

                            if(country.dot) {
                                dot = $('#nxMapService .label[data-for="' + country.id + '"]');
                                if(dot.length) {
                                    dot.data('top', country.dot.t).data('left', country.dot.l).data('active', 0);
                                    gClass = ' g-active';
                                }
                            }

                            svgObj += '<g data-active="0" id="' + country.id + '" class="' + SVG.countries.class.group + gClass + '">';
                            $.each(country.items, function(i, value){
                                svgObj += nxSvgObject( SVG.countries.class.item, value);
                            });
                            svgObj += '</g>';


                        });

                        svgObj += '</g>';
                    }

                    svgObj += '</svg>';
                    target.html(svgObj);

                    if(Modernizr.mq('only all')){
                        nxLabelsPosition();
                        $(window).resize(function() {
                            nxLabelsPosition();
                        });
                    }
                }
            });
        }

        function nxMapShowLabel(target) {
            var id = target.attr('id');
            opt = parseInt(target.data('active'));

            if(!opt) nxVisible(id, true);
            else nxVisible(id, false);

            $('#nxMapService .label').each(function() {
                var lid = $(this).attr('data-for');
                if(lid != id) nxVisible(lid, false);
            });
        }

        function nxMapLabelHover(target) {
            var id = target.attr('data-for');
            opt = parseInt(target.data('active'));

            if(!opt) nxVisible(id, true);
            else nxVisible(id, false);

            $("#nxMapService .label").each(function() {
                var lid = $(this).attr('data-for');
                if(lid != id) nxVisible(lid, false);
            });
        }

        if (Modernizr.touchevents) {
            $('body').on("touchend", '#NxMapCountryLayer g.g-active', function(){nxMapShowLabel($(this));});
        }
        else {
            $('body').on('hover', '#NxMapCountryLayer g.g-active', function(){nxMapShowLabel($(this));});
            $('body').on('hover', '#nxMapService .label', function(){nxMapLabelHover($(this));});
        }

        nxSvgMap ($('#nxMap'));
    });
})(jQuery);