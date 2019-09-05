    <div class="container">
        <ul class="nav nav-tabs" id="graphTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="hourly-graph-tab" data-toggle="tab" href="#hourly-graph" role="tab" aria-controls="hourly-graph" aria-selected="true">Hourly Graph</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="daily-graph-tab" data-toggle="tab" href="#daily-graph" role="tab" aria-controls="daily-graph" aria-selected="false">Daily Graph</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="monthly-graph-tab" data-toggle="tab" href="#monthly-graph" role="tab" aria-controls="monthly-graph" aria-selected="false">Monthly Graph</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="hourly-graph" role="tabpanel" aria-labelledby="hourly-graph-tab">
                <div id="hourlyNetworkTrafficGraph" style="height: 300px;"></div>
            </div>

            <div class="tab-pane fade" id="daily-graph" role="tabpanel" aria-labelledby="daily-graph-tab">
                <div id="dailyNetworkTrafficGraph" style="height: 300px;"></div>
            </div>

            <div class="tab-pane fade" id="monthly-graph" role="tabpanel" aria-labelledby="monthly-graph-tab">
                <div id="monthlyNetworkTrafficGraph" style="height: 300px;"></div>
            </div>
        </div>
    </div>
