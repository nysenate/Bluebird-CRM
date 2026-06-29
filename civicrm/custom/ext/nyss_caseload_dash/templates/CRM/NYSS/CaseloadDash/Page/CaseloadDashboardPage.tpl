<div id="bootstrap-theme" class="nyss-caseload-dash">
    <div class="section nyss-caseload-dash-intro-section">
        <h2><i class="crm-i fa-tachometer" role="img" aria-hidden="true"></i> {ts}Welcome to Your Caseload Dashboard{/ts}</h2>
        <div class="content">{ts}A real-time snapshot of your district's caseload health. At a glance, track aging cases, monitor case manager workload, and ensure that cases are being brought to a timely resolution.{/ts} {help id="id-dashboard_intro" title="{ts}Caseload Dashboard{/ts}"}</div>
        <div class="clear"></div>
    </div>
    <crm-angular-js modules="afsearchNYSSCaseloadOverviewTotals">
        <form>
            <afsearch-n-y-s-s-caseload-overview-totals></afsearch-n-y-s-s-caseload-overview-totals>
        </form>
    </crm-angular-js>

    <crm-angular-js modules="afsearchNYSSCaseloadCaseManagerTotals">
        <div>
            <form>
                <afsearch-n-y-s-s-caseload-case-manager-totals></afsearch-n-y-s-s-caseload-case-manager-totals>
            </form>
        </div>
    </crm-angular-js>
</div>

