<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" data-callback="modalChooseCategoryCallback">
        <div class="modal-flex__content mh-200">
            <div class="row mr-0">
                <div class="col-12 initial-b_i" id="modal_choose_category">
                    <select id="category_type" data-level="1">
                        <option value="">Select category type</option>
                        <option value="1">Product</option>
                        <option value="2">Motor</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><i class="ep-icon ep-icon_ok"></i> Confirm</button>
            </div>
        </div>
    </form>
</div>

<script>
    $(document).ready(function(){
        $('select#category_type').on('change', function(){
            $('#modal_choose_category').find('select[name="category"]').remove();
            var category_type = $(this).val();
            if(category_type == ''){
                return false;
            }

            get_categories(category_type, 1, 0, 'fetch_categories');
        });

        $('#modal_choose_category').on('change', 'select[name="category"]', function(){
            var category_type = $(this).data('type');
            var category_level = $(this).data('level');
            var category = $(this).val();
            if(category_type == ''){
                return false;
            }

            if(category == ''){
                return false;
            }

            $('#modal_choose_category').find('select[name="category"]').each(function (){
                var $this = $(this);
                var level = $this.data('level');

                if(level > category_level)
                    $this.remove();
            });

            get_categories(category_type, category_level, category, 'fetch_categories');
        });
    });

    function fetch_categories(categories, category_type, level){
        var template = '<select class="mt-5" name="category" data-type="'+category_type+'" data-level="'+level+'">';
        template += '<option value="">Select category</option>';
        $.each(categories, function(index, category){
            template += '<option value="'+category.category_id+'">'+category.name+'</option>';
        });
        template += '</select>';
        $('#modal_choose_category').append(template);
    }
</script>
