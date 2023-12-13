<script>
    (function(root, $, baseUrl, companyId, userId) {
        var Wall = function(params) {
            this.scroll = 0;
            this.params = params || {};
            this.user = userId;
            this.ofset = this.params.initialOffset || 0;
            this.wallItemWrapper = $(this.params.itemWrapper || null);
            this.btnLoadWallItems = $(this.params.loadButton || null);
            this.wrapperBtnLoadWallItems = $(this.params.loadButtonWrapper || null);
        }

        Wall.prototype.removeLoadmore = function() {
            this.wrapperBtnLoadWallItems.remove();
        };
        Wall.prototype.showLoader = function() {
            showLoader(this.wrapperBtnLoadWallItems, 'Loading');
        };
        Wall.prototype.hideLoader = function() {
            hideLoader(this.wrapperBtnLoadWallItems);
        };
        Wall.prototype.getOffset = function() {
            return this.ofset;
        };
        Wall.prototype.addOffset = function(offset) {
            this.ofset = this.ofset + offset;
        };
        Wall.prototype.loadItems = function() {
            var url = baseUrl + companyId;
            var data = { offset: this.getOffset() };
            var onRequestSuccess = function(response) {
                if(response.mess_type !== 'success') {
                    systemMessages(response.message, response.mess_type);

                    return;
                }

                if(typeof response.items !=='undefined'){
                    var items = response.items;
                    var total = response.items.length;
                    if(0 !== total) {
                        this.wallItemWrapper.append(items);
                        this.addOffset(total);
                        if(!response.hasMore) {
                            this.removeLoadmore();
                        }
                    }
                }
            };
            var onRequestEnds = function() {
                $(window).scrollTop(this.scroll);
                this.hideLoader();
            }

            this.showLoader();
            this.scroll = $(window).scrollTop();

            return $.get(url, data, null, 'json')
                .then(onRequestSuccess.bind(this))
                .fail(onRequestError)
                .always(onRequestEnds.bind(this));
        };

        root.Wall = Wall;
    } (window, jQuery, __site_url + 'wall/ajax_operations/load/', intval('<?php echo (int) $company['id_company'];?>'), null));

    $(function() {
        var loadMore = function(wall, button) {
            wall.loadItems();
        };

        var more = $('#js-btn-load-more-wall-items');
        var wall = new Wall({
            initialOffset: intval('<?php echo count($wall_items); ?>'),
            itemWrapper: '#js-wrapper-wall-items',
            loadButton: '#js-btn-load-more-wall-items',
            loadButtonWrapper: '#js-wrapper-btn-load-more-wall-items',
        });

        mix(window, { loadMoreWallItems: loadMore.bind(loadMore, wall) });
    });
</script>

<div id="js-wrapper-wall-items">
    <?php foreach ($wall_items as $wall_item) {?>
        <?php echo $wall_item;?>
    <?php } ?>
</div>

<?php if ($has_more_wall_items) {?>
    <div id="js-wrapper-btn-load-more-wall-items" class="relative-b">
        <button class="btn btn-block btn-default call-function" id="js-btn-load-more-wall-items"
        <?php echo addQaUniqueIdentifier('seller__wall_item_load-more_btn'); ?>
        data-callback="loadMoreWallItems"><?php echo translate('seller_home_page_load_more_wall_items_btn');?></button>
    </div>
<?php }?>
