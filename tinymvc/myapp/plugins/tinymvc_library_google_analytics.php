<?php

/**
 * @author Bendiucov Tatiana
 * @todo Refactoring [02.12.2021]
 * library refactoring code style
 */
class TinyMVC_Library_Google_Analytics
{
    private $googleClient;
    private $analyticReporting;
    private $viewId = '76638798';
    private $analyticReportingRequest;
    private $request;

    public function __construct()
    {
        $this->googleClient = new Google_Client();
        $this->googleClient->setAuthConfig(TMVC_MYAPPDIR . '/configs/service_account_secrets.json');
        $this->googleClient->addScope(Google_Service_Analytics::ANALYTICS_READONLY);
        $this->analyticReporting = new Google_Service_AnalyticsReporting($this->googleClient);
        $this->analyticReportingRequest = new Google_Service_AnalyticsReporting_ReportRequest();
        $this->analyticReportingRequest->setViewId($this->viewId);
        $this->request = new Google_Service_AnalyticsReporting_GetReportsRequest();
    }

    public function get_report($params = array())
    {
        extract($params);
        if(!empty($date)){
            $this->set_date_range($date);
        }

        if(!empty($metrics)){
            $this->set_metrics($metrics);
        }

        if(!empty($dimensions)){
            $this->set_dimensions($dimensions);
        }

        if(!empty($dimension_filters)){
            $this->set_dimension_filters($dimension_filters);
        }

        $this->request->setReportRequests( array( $this->analyticReportingRequest));
        return $this->_fetch($this->analyticReporting->reports->batchGet( $this->request ));
    }

    // Create the DateRange object.
    private function set_date_range($date = array()){
        if(empty($date)){
            return;
        }

        $dateRange = new Google_Service_AnalyticsReporting_DateRange();
        if(!empty($date['start'])){
            $dateRange->setStartDate($date['start']);
        }

        if(!empty($date['end'])){
            $dateRange->setEndDate($date['end']);
        }

        $this->analyticReportingRequest->setDateRanges($dateRange);
    }

    private function set_metrics($metrics = array()){
        if(empty($metrics)){
            return;
        }

        $_request_metrics = array();
        foreach ($metrics as $key => $metric) {
            $_request_metrics[$key] = new Google_Service_AnalyticsReporting_Metric();
            $_request_metrics[$key]->setExpression($metric['name']);

            if(isset($metric['alias'])){
                $_request_metrics[$key]->setAlias($metric['alias']);
            }
        }

        if(empty($_request_metrics)){
            return;
        }

        $this->analyticReportingRequest->setMetrics($_request_metrics);
    }

    private function set_dimensions($dimensions = array()){
        if(empty($dimensions)){
            return;
        }

        $_request_dimensions = array();
        foreach ($dimensions as $key => $dimension) {
            $_request_dimensions[$key] = new Google_Service_AnalyticsReporting_Dimension();
            $_request_dimensions[$key]->setName($dimension);
        }

        if(empty($_request_dimensions)){
            return;
        }

        $this->analyticReportingRequest->setDimensions($_request_dimensions);
    }

    private function set_dimension_filters($dimension_filters = array()){
        if(empty($dimension_filters)){
            return;
        }

        $_request_dimension_filters = array();
        foreach ($dimension_filters as $key => $dimension_filter) {
            $_request_dimension_filters[$key] = new Google_Service_AnalyticsReporting_DimensionFilter();
            $_request_dimension_filters[$key]->setDimensionName($dimension_filter['name']);
            $_request_dimension_filters[$key]->setOperator($dimension_filter['operator']);
            $_request_dimension_filters[$key]->setExpressions(array($dimension_filter['expressions']));

        }

        // Create the DimensionFilterClauses
        $dimension_filter_clause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
        $dimension_filter_clause->setFilters($_request_dimension_filters);

        $this->analyticReportingRequest->setDimensionFilterClauses($dimension_filter_clause);
    }

    private function _fetch($reports) {
        $results = array();
        for ( $reportIndex = 0; $reportIndex < count( $reports ); $reportIndex++ ) {
            $report = $reports[ $reportIndex ];
            $header = $report->getColumnHeader();
            $dimensionHeaders = $header->getDimensions();
            $metricHeaders = $header->getMetricHeader()->getMetricHeaderEntries();
            $rows = $report->getData()->getRows();
            $totals = $report->getData()->getTotals();

            for ( $rowIndex = 0; $rowIndex < count($rows); $rowIndex++) {
                $row = $rows[ $rowIndex ];
                $dimensions = $row->getDimensions();
                $metrics = $row->getMetrics();
                for ($i = 0; $i < count($dimensionHeaders) && $i < count($dimensions); $i++) {
                    $results['targets'][$rowIndex]['dimensions'][$dimensionHeaders[$i]] = $dimensions[$i];
                }

                for ($j = 0; $j < count($metrics); $j++) {
                    $values = $metrics[$j]->getValues();
                    for ($k = 0; $k < count($values); $k++) {
                        $entry = $metricHeaders[$k];
                        $results['targets'][$rowIndex]['metrics'][$entry->getName()] = $values[$k];
                    }
                }
            }

            for ( $rowIndex = 0; $rowIndex < count($totals); $rowIndex++) {
                $values = $totals[$rowIndex]->getValues();
                for ($k = 0; $k < count($values); $k++) {
                    $entry = $metricHeaders[$k];
                    $results['totals']['metrics'][$entry->getName()] = $values[$k];
                }
            }

        }

        return $results;
    }
}
