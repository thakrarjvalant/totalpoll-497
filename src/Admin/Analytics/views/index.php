
<?php ! defined('ABSPATH') && exit(); ?>
<div id="totalpoll-analytics" class="wrap totalpoll-page" ng-app="analytics">
    <h1 class="totalpoll-page-title"><?php _e('Analytics', 'totalpoll'); ?></h1>
    <analytics-browser></analytics-browser>
    <script type="text/ng-template" id="analytics-browser-component-template">
        <div class="totalpoll-analytics" ng-class="{'totalpoll-processing': $ctrl.isProcessing()}">
            <div class="totalpoll-box totalpoll-analytics-header">
                <div class='exp-btn-div'>
                    <a href="<?php echo admin_url("edit.php?post_type=poll&page=analytics&action=download") ?>" class="button button-large export-csv-btn">
                        Export All Users Resolution
                    </a>
                </div>
            </div>
        </div>
        <div class="totalpoll-analytics" ng-class="{'totalpoll-processing': $ctrl.isProcessing()}">
            <div class="totalpoll-box totalpoll-analytics-header">
                <div class="totalpoll-analytics-header-polls">
                    <select class='poll-dropdown' ng-model="$ctrl.filters.poll" ng-options="poll.id as poll.title for poll in $ctrl.polls" ng-change="$ctrl.loadMetrics()">
                        <option value=""><?php _e('Please select a poll', 'totalpoll'); ?></option>
                    </select>
                </div>
                <div class='exp-btn-div'>
                  <button class="button button-large export-pdf-btn" id='export-pdf-btn'>
                        PDF Export
                  </button>
                </div>
            </div>
        </div>

        <div class="totalpoll-analytics-charts">
          <div class="totalpoll-row" ng-if="$ctrl.metrics.resolution">
              <div class="totalpoll-column">
                  <div class="totalpoll-box">
                    <div class="poll-chart-title"></div>
                    <div><center><h2>Total Voting Percentage</h2></center></div>
                      <div class="totalpoll-box-section">
                          <chart type="pie" ng-model="$ctrl.metrics.resolution"></chart>
                          <div class='chart-table' id='resolution-table'></div>
                      </div>
                  </div>
              </div>
          </div>
          <div class="totalpoll-row" ng-if="$ctrl.metrics.votes">
              <div class="totalpoll-column">
                  <div class="totalpoll-box">
                    <div class="poll-chart-title"></div>
                    <div><center><h2>Total Vote Count</h2></center></div>
                      <div class="totalpoll-box-section">
                          <chart type="pie" ng-model="$ctrl.metrics.votes"></chart>
                          <div class='chart-table' id='vote-count-table'></div>
                      </div>
                  </div>
              </div>
          </div>

          <!-- <div class="totalpoll-row" ng-if="$ctrl.metrics.votes">
              <div class="totalpoll-column">
                  <div class="totalpoll-box">
                    <div><center><h2>Chart based on users vote count</h2></center></div>
                      <div class="totalpoll-box-section">
                          <chart type="bar" ng-model="$ctrl.metrics.votes"></chart>
                      </div>
                  </div>
              </div>
          </div> -->
        </div>
        </div>
    </script>
</div>
