OpencastPageComponent = {

    max_width: 1000,

    slider: null,

    overwriteResetButton: function(name, url) {
        $('input[name="cmd[resetFilter]"]').replaceWith('<a class="btn btn-default" href="' + url + '">' + name + '</a>');
    },

    initForm: function(max_width) {
        this.max_width = parseInt(max_width) ?? 1000;
        OpencastPageComponent.slider = $("#ocpc_slider").data("ionRangeSlider");
        OpencastPageComponent.updateSlider();

        $('input#prop_size_width').change(function() {
            let new_width = $(this).val();
            new_width = parseInt(new_width);
            if (OpencastPageComponent.keepAspectRatio()) {
                let current_width = $('#ocpc_thumbnail').width();
                current_width = parseInt(current_width);
                let current_height = $('#ocpc_thumbnail').height();
                current_height = parseInt(current_height);
                let ratio = (current_width / current_height);
                let new_height = new_width / ratio;
                $('#ocpc_thumbnail').height(new_height);
                $('input#prop_size_height').val(new_height);
            }
            $('#ocpc_thumbnail').width(new_width);
            OpencastPageComponent.updateSlider();
        });

        $('input#prop_size_height').change(function() {
            let new_height = $(this).val();
            new_height = parseInt(new_height);
            if (OpencastPageComponent.keepAspectRatio()) {
                let current_width = $('#ocpc_thumbnail').width();
                current_width = parseInt(current_width);
                let current_height = $('#ocpc_thumbnail').height();
                current_height = parseInt(current_height);
                let ratio = (current_width / current_height);
                let new_width = new_height * ratio;
                $('#ocpc_thumbnail').width(new_width);
                $('input#prop_size_width').val(new_width);
                OpencastPageComponent.updateSlider();
            }
            $('#ocpc_thumbnail').height(new_height);
        });
    },

    updateSlider: function() {
        let width = $('input#prop_size_width').val();
        width = parseInt(width);
        if (parseInt(width) > parseInt(OpencastPageComponent.max_width)) {
            console.log(width);
            console.log(OpencastPageComponent.max_width);
            OpencastPageComponent.max_width = width;
        }
        let percentage = (width / OpencastPageComponent.max_width) * 100;
        OpencastPageComponent.slider.update({from: percentage});
    },

    sliderCallback: function(data) {
        let current_width = $('#ocpc_thumbnail').width();
        current_width = parseInt(current_width);
        let current_height = $('#ocpc_thumbnail').height();
        current_height = parseInt(current_height);
        let ratio = (current_width / current_height);
        let percentage = parseInt(data.from);

        let new_width = OpencastPageComponent.max_width * (percentage / 100);
        let new_height = (new_width / ratio);

        $('#ocpc_thumbnail').width(new_width);
        $('input#prop_size_width').val(new_width);
        $('#ocpc_thumbnail').height(new_height);
        $('input#prop_size_height').val(new_height);
    },

    keepAspectRatio: function() {
        return $('input#prop_size_constr').is(":checked");
    },
}
