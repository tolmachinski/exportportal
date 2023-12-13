<div class="container-1420 calendar">
    <div class="calendar__header-ttl section-header section-header--title-only">
        <h2 class="section-header__title"><?php echo translate('calendar_ttl'); ?></h2>
    </div>
    <header class="calendar__header">
        <nav class="calendar__navbar">
            <button class="calendar__navbar-btn calendar__navbar-btn--prev js-calendar-button call-action" data-js-action="calendar-prev-month:click" type="button">
                <?php echo widgetGetSvgIcon('arrow-prev', 11, 11); ?>
            </button>
            <button class="calendar__navbar-btn calendar__navbar-btn--next js-calendar-button call-action" data-js-action="calendar-next-month:click" type="button">
                <?php echo widgetGetSvgIcon('arrow-next', 11, 11); ?>
            </button>
            <h3 class="calendar__navbar-title js-calendar-month-title"></h3>
            <span class="navbar--range"></span>
        </nav>
        <button class="calendar__navbar-btn btn calendar__navbar-btn-today js-calendar-button call-action" data-js-action="calendar-today-month:click" type="button"><?php echo translate('calendar_today'); ?></button>
    </header>
    <main id="calendar" class="calendar__main"></main>
</div>

<?php encoreEntryLinkTags('calendar_page'); ?>
<?php encoreEntryScriptTags('calendar_page'); ?>
