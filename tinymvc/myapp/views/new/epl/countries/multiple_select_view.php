<script>
var shipperCountriesAllChange = false;
var shipperCountriesInnerChange = false;

$(function(){
    let mainCountriesChecbox = $('.multiple-epselect-countries__top input[type="checkbox"]');
    let countriesCheckbox = $('.multiple-epselect-countries__inner input[type="checkbox"]');

    initCategoriesCheckbox();

    $('body').on('click', function(e){
        var $wr = $('.multiple-epselect-countries__list-wr');
        var $choice = $('.multiple-epselect-countries');

        if ($(e.target)[0] === $choice[0] || $(e.target).parents('.multiple-epselect-countries')[0] === $choice[0]) {
            return;
        }

        if (($(e.target)[0] === $wr[0] || $(e.target).parents('.multiple-epselect-countries__list-wr')[0] !== $wr[0]) &&
            $wr.is(':visible')) {

            $('.multiple-epselect-countries__list-wr').slideUp();
        }
    });

    $('.multiple-epselect-countries__input').on('click', function(e){
        $(this).next('.multiple-epselect-countries__list-wr').slideToggle();
    });

    mainCountriesChecbox.on('change', function(){
        if(!shipperCountriesInnerChange){
            console.log(shipperCountriesInnerChange)
            shipperCountriesAllChange = true;
            shipperCountriesInnerChange = true;

            var $this = $(this);
            multipleEpselectTopCheckbox($this);

			shipperCountriesAllChange = false;
			shipperCountriesInnerChange = false;
		}
    });

    countriesCheckbox.on('change', function(){
        if(!shipperCountriesAllChange){
            shipperCountriesInnerChange = true;

            var $this = $(this);
            multipleEpselectInnerCheckbox($this);

			shipperCountriesInnerChange = false;
		}
    });

    $('body').on('keyup', '#search-country-list input[name=keyword]', function(e){
        e.preventDefault();
        var $this =  $(this);
        var $thisForm = $this.closest('.input-group');
        var keyword = $this.val();

        if(!keyword.length){
            resetCountrySearch();
            return false;
        }else{
            searchCountry($thisForm, keyword);
        }
    });
});

function searchCountry($thisForm, keyword){
    var $countryList = $('#js-shipper-countries-wr');
    $countryList.find('li.parent .ep-icon_plus-stroke').trigger('click');
    $thisForm.find(".input-group-append").removeClass('display-n');

    var bgChange = false;
    var regex = new RegExp(keyword, "gi");
    $countryList.find(".multiple-epselect-countries__inner li:not(:hidden)").addClass('display-n');

    $countryList.find( ".multiple-epselect-countries__inner li" ).each(function () {
        var $this = $(this);
        var $thisSpan = $this.find('.name');
        var good = $thisSpan.text().search(regex);

        if(good != -1){
            bgChange = true;
            $this.removeClass('display-n');
        }
    });

    $countryList.find( "li.parent" ).each(function () {
        var $parentCountry = $(this);
        var countCountry = $(this).find(".multiple-epselect-countries__inner li:not(:hidden)").length;

        if(countCountry == 0){
            $parentCountry.addClass('display-n');
        }else{
            $parentCountry.removeClass('display-n');
        }
    });

    if(!bgChange)
        systemMessages('<?php echo translate('systmess_company_info_country_not_found_message'); ?>', 'message-info' );
}

function resetCountrySearch(){
    var $countryList = $('#js-shipper-countries-wr');
    $countryList.find('li.display-n').removeClass('display-n');
    $countryList.find('li.parent .ep-icon_remove-stroke').trigger('click');

    var $thisForm = $('#search-country-list');
    $thisForm.find(".input-group-append").addClass('display-n');
    $thisForm.find('input[type=text]').val('');
}

var multipleToggleCategories = function($this){
    var idIndustries = $this.closest('.parent').data('industry');
    var $inner = $this.closest('.multiple-epselect-countries__top').next('.multiple-epselect-countries__inner');

    $this.toggleClass('ep-icon_plus-stroke ep-icon_remove-stroke');
    $inner.slideToggle();
}

var multipleEpselectInnerCheckbox = function($this){
    var $inner = $this.closest('.multiple-epselect-countries__inner');
    var countchekbox = 0;
    var countchecked = 0;

    $inner.find('li').each(function(){
        var $thisLi = $(this);
        var $checkbox = $thisLi.find('input[type="checkbox"]');

        countchekbox++;

        if($checkbox.prop('checked')){
            countchecked++;
        }
    });

    if(countchekbox == countchecked){
        if($this.closest('.parent').find('.multiple-epselect-countries__top input[type="hidden"]').length){
            $this.closest('.parent').find('.multiple-epselect-countries__top input[type="hidden"]').remove();
        }
        $inner.prev('.multiple-epselect-countries__top').find('input[type="checkbox"]').removeClass('indeterminate').prop('checked', true);
    }else if(countchecked){
        if(!$this.closest('.parent').find('.multiple-epselect-countries__top input[type="hidden"]').length){
            var parentVal = $this.closest('.parent').data('industry');
            $inner.prev('.multiple-epselect-countries__top').append('<input type="hidden" name="industriesSelected[]" value="' + parentVal + '">');
        }
        $inner.prev('.multiple-epselect-countries__top').find('input[type="checkbox"]').addClass('indeterminate').prop('checked', false);
    }else{
        if($this.closest('.parent').find('.multiple-epselect-countries__top input[type="hidden"]').length){
            $this.closest('.parent').find('.multiple-epselect-countries__top input[type="hidden"]').remove();
        }
        $inner.prev('.multiple-epselect-countries__top').find('input[type="checkbox"]').removeClass('indeterminate').prop('checked', false);
    }

    countSelectedCountries();
}

var multipleEpselectTopCheckbox = function($this){
    var idIndustries = $this.val();
    var $inner = $this.closest('.multiple-epselect-countries__top').next('.multiple-epselect-countries__inner');

    if($this.closest('.parent').find('.multiple-epselect-countries__top input[type="hidden"]').length){
        $this.closest('.parent').find('.multiple-epselect-countries__top input[type="hidden"]').remove();
    }

    $this.removeClass('indeterminate');
    $inner.find('input[type="checkbox"]').prop('checked', $this.prop('checked'));

    countSelectedCountries();
}

var countSelectedCountries = function(){
    var countSelected = 0;

    $('.multiple-epselect-countries__inner li').each(function(){
        var $thisLi = $(this);
        var $checkbox = $thisLi.find('input[type="checkbox"]');

        if(String($checkbox.prop('checked')) == "true"){
            countSelected++;
        }
    });

    $('.multiple-epselect-countries__input').text('Selected ' + countSelected + ' countries');
}

var initCategoriesCheckbox = function(){
    $('.multiple-epselect-countries__list li.parent').each(function(){
        var $thisLi = $(this);
        var countchekbox = 0;
        var countchecked = 0;

        if($thisLi.find('.multiple-epselect-countries__inner li').length){
            $thisLi.find('.multiple-epselect-countries__inner li').each(function(){
                var $thisLicheckbox = $(this);
                var $checkbox = $thisLicheckbox.find('input[type="checkbox"]');

                countchekbox++;

                if($checkbox.prop('checked')){
                    countchecked++;
                }
            });

            if(countchecked != 0){

                if( countchecked == countchekbox ){
                    $thisLi.find('.multiple-epselect-countries__top input[type="checkbox"]').removeClass('indeterminate').prop('checked', true);
                }else{
                    $thisLi.find('.multiple-epselect-countries__top input[type="checkbox"]').addClass('indeterminate').prop('checked', false);
                }

            }
        }
    });
};
</script>

<div class="multiple-epselect-countries" <?php echo addQaUniqueIdentifier('ff-select-location__multiple-select-countries-div');?>>
    <div class="multiple-epselect-countries__input">
        <?php if(empty($array_countries_selected)){?>
            Select countries
        <?php }else{ ?>
            Selected <?php echo count($array_countries_selected);?> countries
        <?php } ?>
    </div>
    <div class="multiple-epselect-countries__list-wr">
        <ul id="js-shipper-countries-wr" class="multiple-epselect-countries__list">
            <li id="search-country-list">
                <input class="form-control" type="text" name="keyword" placeholder="Search countries: e.g. United States of America" <?php echo addQaUniqueIdentifier('ff-select-location__multiple-select-search-input');?>>
                <div class="input-group-append display-n">
                    <button class="btn btn-default call-function" data-callback="resetCountrySearch" type="button" <?php echo addQaUniqueIdentifier('ff-select-location__multiple-select-reset-search-btn');?>><i class="ep-icon ep-icon_remove-stroke"></i></button>
                </div>
            </li>

            <?php $array_countries_selected = empty($array_countries_selected) ? array() : $array_countries_selected;?>

            <?php foreach($countries_by_continents as $continent){?>
                <li class="parent" data-industry="<?php echo $continent['id_continent'];?>">
                    <div class="multiple-epselect-countries__top">
                        <label class="custom-checkbox" <?php echo addQaUniqueIdentifier('ff-select-location__select-continent-checkbox-' . $continent['id_continent']);?>>
                            <input type="checkbox" name="industriesSelected[]" value="<?php echo $continent['id_continent']?>">
                            <span class="custom-checkbox__text"><?php echo $continent['name_continent']?></span>
                        </label>
                        <i class="ep-icon ep-icon_plus-stroke call-function" data-callback="multipleToggleCategories" <?php echo addQaUniqueIdentifier('ff-select-location__toggle-countries-btn-' . $continent['id_continent']);?>></i>
                    </div>
                    <ul class="multiple-epselect-countries__inner">
                        <?php foreach($continent['countries'] as $country){?>
                            <li>
                                <label class="custom-checkbox" data-value="<?php echo $country['id']?>" data-continent="<?php echo $country['id_continent']?>" <?php echo addQaUniqueIdentifier('ff-select-location__select-country-checkbox-' . $country['id']);?>>
                                    <input type="checkbox" name="countriesSelected[]" value="<?php echo $country['id']?>" <?php echo in_array($country['id'], $array_countries_selected) ? 'checked' : '';?>>
                                    <img
                                        class="ml-10"
                                        width="24"
                                        height="24"
                                        src="<?php echo getCountryFlag($country['country']);?>"
                                        alt="<?php echo $country['country']?>"
                                        title="<?php echo $country['country']?>"
                                    />
                                    <span class="custom-checkbox__text"><?php echo $country['country']?></span>
                                </label>
                            </li>
                        <?php } ?>
                    </ul>
                </li>
            <?php }?>
        </ul>
    </div>
</div>
