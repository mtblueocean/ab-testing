jQuery(function ($) {

    Product = Backbone.Model.extend({
        defaults: {
            "updating": false
        }
    });

    Products = Backbone.Collection.extend({

        parse: function (data) {
            this.url = data.next_page;
            return data.products;
        },

        model: Product
    });

    ProductView = Backbone.View.extend({
        tagName: 'tr',

        className: 'wcro-item',

        isUpdating: false,

        events: {
            "change input[name='quantity']": "updateQuantity",
            "wcro-enter input[name='quantity']": "updateQuantity",
            "keydown .cart": "updateOnEnter",
            "change input[name='wcro-sub-box']": "updateSub"
        },

        initialize: function () {
            this.$el.attr('id', 'wcro_item_' + this.model.get('id'));
            if(!this.model.get('in_stock')){
                this.$el.addClass('wcro_no_stock');
            }
            this.template = _.template($("#productTemplate").html());
            this.model.set('original_price', this.model.get('price'));
            
        },

        render: function () {
            var data = _.extend(this.model.attributes, {totalPrice: this.totalPrice()});
            
            this.$el.html(this.template(data));

            // Add class if featured
            if (this.model.get('featured')) {
                this.$el.addClass('wcro_featured');
            }

            // Add class if on sale
            if (this.model.get('on_sale')) {
                this.$el.addClass('wcro_sale');
            }

            if (this.$el.find('input[name="wcro-sub"]').val() == 'yes') {
                this.$el.find('input[name="wcro-sub-box"]').prop('checked', true);
            }

            return this;
        },

        updating: function (isUpdating) {
            this.isUpdating = isUpdating;

            if (isUpdating) {
                this.$el.find('.wcro-item-loader').fadeIn();
            } else {
                this.$el.find('.wcro-item-loader').hide();
            }
        },

        updateOnEnter: function (evt) {
            if (evt.keyCode == 13) {
                evt.preventDefault();
                this.updateQuantity(evt);
            }
        },

        updateQuantity: function (evt) {
            this.updatePrice(this.calculatePrice($(evt.currentTarget).val()));
            var me = this;
            this.updating(true);
            clearTimeout(this.timeout);
            this.$el.find('.wcro-item-loader').fadeIn();
            this.timeout = setTimeout(function () {
                var hash = me.model.get('cart_item_hash');
                var $current = $(evt.currentTarget);
                
                var value = parseFloat($current.val());
                if(isNaN(value)) value = 0;
                var $form = $current.closest("form");
                if (hash) {
                    // we need to update the quantity instead of add it.
                    var data = {
                        cart_item_key: hash,
                        quantity: value,
                        update_cart: true
                    };
                    
                    $.ajax({
                        url: wcro.wc_ajax_url.replace('%%endpoint%%', 'wcro_update_cart'),
                        method: "POST",
                        data: data
                    }).success(function (data) {
                        me.cartUpdated(data);
                        if(value <= 0) me.model.set('cart_item_hash', '');
                        me.discountMessage(data.discount);
                    }).done(function () {
                        me.updateCartFragment();
                    });
                } else if(value > 0){
                    $.ajax({
                        url: wcro.wc_ajax_url.replace('%%endpoint%%', 'wcro_add_to_cart'),
                        method: "POST",
                        data: $form.serialize()
                    }).success(function (data) {
                        me.model.set('cart_item_hash', data.cart_item_hash);
                        me.cartUpdated(data);
                        me.discountMessage(data.discount);
                    }).done(function () {
                        me.updateCartFragment();
                    });
                } else {
                    me.$el.find('.wcro-item-loader').hide();
                }

            }, 550);
        },

        updateSub: function (evt) {
            var me = this;
            var hash = this.model.get('cart_item_hash');
            var val = $(evt.currentTarget).prop('checked') ? 'yes' : 'no';
            this.$el.find('input[name="wcro-sub"]').val(val);
            this.$el.find('.wcro-item-loader').fadeIn();
            if (hash) {
                // Remove item, and re-post.
                var data = {
                    cart_item_key: hash,
                    quantity: 0,
                    update_cart: true
                };
                
                // Set the quantity to 0 to 'remove' it
                $.ajax({
                    url: wcro.wc_ajax_url.replace('%%endpoint%%', 'wcro_update_cart'),
                    method: "POST",
                    data: data
                }).success(function (data) {
                    me.model.set('cart_item_hash', data.cart_item_hash);
                    
                    // set it back to the desired amount
                    $.ajax({
                        url: wcro.wc_ajax_url.replace('%%endpoint%%', 'wcro_add_to_cart'),
                        method: "POST",
                        data: me.$el.find('.cart').serialize()
                    }).success(function (data) {
                        me.model.set('cart_item_hash', data.cart_item_hash);
                        me.cartUpdated(data);
                    }).done(function () {
                        me.updateCartFragment();
                    });
                });
            } else {
                // do nothing, there's no item in the cart yet
                this.$el.find('.wcro-item-loader').hide();
            }
        },

        cartUpdated: function (data) {
            if(data.total) this.updatePrice(data.total);
            if(data.price) this.model.set('price', data.price);
            
            this.$el.find('.wcro_total .wcro-cart-action').fadeIn().delay(200).fadeOut('slow');
        },

        // todo implement this on the server side to show appropriate discount
        discountMessage: function(amount){
            if(amount > 0){
                this.$el.find('.wcro_total .wcro-cart-discount').html('Saved '+wcro.currency_symbol +amount);
                this.$el.find('.wcro_total .wcro-cart-discount').show();
            } else {
                this.$el.find('.wcro_total .wcro-cart-discount').hide();
            }
            
        },

        calculatePrice: function(qty){
            var me = this;
            
            _.each(this.model.get('price_adjusters'), function(name){
                if (window.hasOwnProperty(name)) {
                    me.model.set('price', window[name](me.model.get('original_price'), me.model.get('price'), qty));
                }
            });
            
            var total = parseFloat(qty * this.model.get('price'));
            if(!total || isNaN(total)) return 0;
            return total.toFixed(wcro.currency_format_num_decimals)
        },

        updatePrice: function (total) {
            total = parseFloat(total).toFixed(wcro.currency_format_num_decimals);
            
            var priceHtml = this.formatTotalPrice(total);
            
            this.$el.find('.wcro_total_price').html(priceHtml);
        },
        
        totalPrice: function () {
            var total = this.model.get('cart_item_total');
            if (total == 0 || !total) return '';
            return this.formatTotalPrice(parseFloat(total).toFixed(wcro.currency_format_num_decimals));
        },
        
        formatTotalPrice: function(total) {
            return accounting.formatMoney( total, {
                symbol:    wcro.currency_format_symbol,
                decimal:   wcro.currency_format_decimal_sep,
                thousand:  wcro.currency_format_thousand_sep,
                precision: wcro.currency_format_num_decimals,
                format:    wcro.currency_format
            } );
        },

        updateCartFragment: function () {
            this.updating(false);
            $.ajax({
                url: wc_cart_fragments_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
                type: 'POST',
                success: function (data) {
                    if (data && data.fragments) {

                        $.each(data.fragments, function (key, value) {
                            $(key).replaceWith(value);
                        });

                        $(document.body).trigger('wc_fragments_refreshed');
                    }
                }
            });
        }
    });

    ProductListView = Backbone.View.extend({
        el: '.wcro-products',

        events: {
            'input input[type="search"]': 'liveSearch'
        },

        liveSearch: function (e) {
            var me = this;
            clearTimeout(this.timeout);
            this.timeout = setTimeout(function () {
                if (!me.displayingSearch) {
                    me.prevData = {
                        next_page: me.collection.url,
                        products: me.collection.models
                    }
                }

                var term = $(e.currentTarget).val();
                if (term.length > 2) {


                    $.get(wcro.base_path, {post_type: 'product', s: term, "wcro-ajax": 1}, function (data) {
                        me.collection.reset(data, {parse: true});
                        me.displayingSearch = true;
                    });

                } else {
                    if (me.displayingSearch) {
                        me.collection.reset(me.prevData, {parse: true});
                    }
                    me.displayingSearch = false;
                }
            }, 150);
        },

        initialize: function () {
            _.bindAll(this, 'checkScroll');
            $(window).scroll(this.checkScroll);

            this.$loader = $('#wcro-loader');
            this.render();
            this.listenTo(this.collection, 'add', this.addOne);
            this.listenTo(this.collection, 'reset', this.reset);
            this.isLoading = false;
            this.displayingSearch = false;
        },

        render: function () {
            this.collection.forEach(this.addOne, this);
        },

        reset: function () {
            this.$el.find('tr.wcro-item').remove();
            this.render();
        },

        addOne: function (product) {
            this.$loader.hide();
            var me = this;
            if (product.get('type') == 'variable') {
                _.each(product.get('variations'), function (variation) {
                    me.collection.add(variation);
                });
            } else {
                var productView = new ProductView({model: product});
                this.$loader.before(productView.render().el);
            }

            // Add support for WooCommerce Quantity Increment buttons
            if (typeof wcqib_refresh_quantity_increments == 'function') {
                wcqib_refresh_quantity_increments()
            }
            
            // initiate swipebox support 
            $( '.wcro-swipebox' ).swipebox({hideCloseButtonOnMobile: false, loopAtEnd: true});
        },

        checkScroll: function () {
            var me = this;
            var triggerPoint = 300; // 200px from the bottom
            var offset = this.$el.position().top + this.el.clientHeight - triggerPoint;
            if (!this.isLoading && this.collection.url && $(window).scrollTop() + window.innerHeight > offset) {
                this.isLoading = true;
                this.$loader.show();
                this.collection.fetch({
                    success: function (resp) {
                        // Now we have finished loading set isLoading back to false
                        me.isLoading = false;
                        me.$loader.hide();
                    },
                    remove: false
                });
            }
        }
    });

    ProductsFooterView = Backbone.View.extend();

    if (typeof WCRO_Items !== 'undefined') {
        
        $('.woocommerce-pagination').hide(); // dont need this, infinite scroll
        
        var products = new Products();
        var listView = new ProductListView({collection: products});

        products.url = WCRO_Items.next_page;
        products.add(WCRO_Items.products);
    }

    $('.tooltip').tooltipster({
        maxWidth: 200,
        theme: 'tooltipster-light',
        position: 'bottom'
    });

    if (wcro.stick_table == 'yes') {
        make_table_sticky();
    }

    function make_table_sticky() {
        var stickArgs = { top: 0, bottom: 0 };
        if (wcro.fixed_header_class != '' && $(wcro.fixed_header_class).length) stickArgs['top'] += $(wcro.fixed_header_class).outerHeight();
        if ($('.admin-bar').length) stickArgs['top'] += $('#wpadminbar').outerHeight();
        
        if ($('.demo_store').length) stickArgs['bottom'] = $('.demo_store').outerHeight();
        $('.wcro-products').stickyHeaderFooter(stickArgs);
    }

    $('.wcro-products .woocommerce-product-search input[type="search"]').attr('autocomplete', 'off');

    $(document).on('keyup', 'input.qty',function(e){
        if(e.keyCode == 13){
            $(this).trigger('wcro-enter');
        }
    });
});
