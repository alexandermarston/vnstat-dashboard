    <div class="container">
        <ul class="nav nav-tabs" id="tableTab" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="hourly-table-tab" data-toggle="tab" href="#hourly-table" role="tab" aria-controls="hourly-table" aria-selected="true">Hourly</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="daily-table-tab" data-toggle="tab" href="#daily-table" role="tab" aria-controls="daily-table" aria-selected="false">Daily</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="monthly-table-tab" data-toggle="tab" href="#monthly-table" role="tab" aria-controls="monthly-table" aria-selected="false">Monthly</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="top10-table-tab" data-toggle="tab" href="#top10-table" role="tab" aria-controls="top10-table" aria-selected="false">Top 10</a>
            </li>
        </ul>

        <div class="tab-content" id="tableTabContent">
            <div class="tab-pane fade show active" id="hourly-table" role="tabpanel" aria-labelledby="hourly-table-tab">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Hour</th>
                            <th>Received</th>
                            <th>Sent</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
{foreach from=$hourlyTableData key=key item=value}
                        <tr>
                            <td>{$value.label}</td>
                            <td>{$value.rx}</td>
                            <td>{$value.tx}</td>
                            <td>{$value.total}</td>
                        </tr>
{/foreach}
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="daily-table" role="tabpanel" aria-labelledby="daily-table-tab">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Received</th>
                            <th>Sent</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
{foreach from=$dailyTableData key=key item=value}
                        <tr>
                            <td>{$value.label}</td>
                            <td>{$value.rx}</td>
                            <td>{$value.tx}</td>
                            <td>{$value.total}</td>
                        </tr>
{/foreach}
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="monthly-table" role="tabpanel" aria-labelledby="monthly-table-tab">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Received</th>
                            <th>Sent</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
{foreach from=$monthlyTableData key=key item=value}
                        <tr>
                            <td>{$value.label}</td>
                            <td>{$value.rx}</td>
                            <td>{$value.tx}</td>
                            <td>{$value.total}</td>
                        </tr>
{/foreach}
                    </tbody>
                </table>
            </div>

            <div class="tab-pane fade" id="top10-table" role="tabpanel" aria-labelledby="top10-table-tab">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Day</th>
                            <th>Received</th>
                            <th>Sent</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
{foreach from=$top10TableData key=key item=value}
                        <tr>
                            <td>{$value.label}</td>
                            <td>{$value.rx}</td>
                            <td>{$value.tx}</td>
                            <td>{$value.total}</td>
                        </tr>
{/foreach}
                    </tbody>
                </table>
            </div>
        </div>
    </div>
