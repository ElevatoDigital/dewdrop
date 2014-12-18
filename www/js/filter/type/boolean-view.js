define(
    function () {
        return Backbone.View.extend({
            render: function () {
                this.$el.append('boolean');

                return this;
            }
        });
    }
);
