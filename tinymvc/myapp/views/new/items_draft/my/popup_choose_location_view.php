<div class="js-modal-flex wr-modal-flex inputs-40">
    <form class="modal-flex__form validateModal" data-callback="modalChooseLocationCallback">
        <div class="modal-flex__content mh-500">
            <fieldset class="pb-10 mr-5">
                <legend>Product location</legend>
                <div class="row">
                    <div class="col-6">
                        <label class="input-label">Country</label>
                        <select id="country" name="port_country">
                            <?php echo getCountrySelectOptions($countries, empty($ep_columns_config['product_country']) ? 0 : $ep_columns_config['product_country']);?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label class="input-label">State</label>
                        <div id="state_td">
                            <select name="states" id="country_states">
                                <option value="">Select state or province</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="input-label"><span class="txt-red">*</span> City</label>
                        <div id="city_td" class="wr-select2-h35 h-50">
                            <select name="port_city" class="select-city" id="port_city">
                                <option value="">Select country first</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <label class="input-label"><span class="txt-red">*</span> ZIP</label>
                        <input type="text" name="zip" class="validate[custom[zip_code],maxSize[20]]" maxlength="20" value="<?php echo $item_draft['item_zip'];?>"/>
                    </div>
                </div>
            </fieldset>
            <fieldset class="pt-10 pb-10 mr-5">
                <legend>Country of origin</legend>
                <div class="row">
                    <div class="col-12">
                        <select name="origin_country">
                            <option value="">Select Country</option>
                            <?php foreach($countries as $country){ ?>
                                <option value="<?php echo $country['id']?>" <?php if(!empty($ep_columns_config['product_country'])){echo selected($ep_columns_config['product_country'], $country['id']);}?>><?php echo $country['country']?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </fieldset>
        </div>
        <div class="modal-flex__btns">
            <div class="modal-flex__btns-right">
                <button class="btn btn-primary" type="submit"><i class="ep-icon ep-icon_ok"></i> Confirm</button>
            </div>
        </div>
    </form>
</div>
<script>
    var $selectCity;
    $(document).ready(function(){
        $selectCity = $(".select-city");
        initSelectCity($selectCity);
    });
</script>
