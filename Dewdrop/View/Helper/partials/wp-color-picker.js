jQuery(function () {
    'use strict';

     var options = {
        defaultColor: "<?php echo $this->escapeJs($this->defaultColor);?>",
        palettes:     <?php echo json_encode($this->palettes);?>
     };

     jQuery('#' + '<?php echo $this->escapeJs($this->id);?>').wpColorPicker(options);
});
