define(
    function () {
        'use strict';

        return Backbone.View.extend({
            render: function () {
                this.$el.append('numeric');

                return this;
            }
        });
    }
);
