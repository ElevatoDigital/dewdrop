jQuery(function () {
    'use strict';

     var options = {
        dateFormat: "<?php echo $this->escapeJs($this->dateFormat);?>"
     };

     jQuery('#' + '<?php echo $this->escapeJs($this->id);?>').datepicker(options);
});
