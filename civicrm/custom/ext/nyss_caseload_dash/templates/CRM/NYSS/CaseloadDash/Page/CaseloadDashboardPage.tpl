<div id="bootstrap-theme" class="nyss-caseload-dash">
<div class="section nyss-caseload-dash-intro-section">
    <h2><i class="crm-i fa-tachometer" role="img" aria-hidden="true"></i> {ts}Welcome to Your Caseload Dashboard{/ts} {help id="id-dashboard_intro"}</h2>
    <div class="content">{ts}A real-time snapshot of your district's caseload health. At a glance, track case aging, monitor workload, and ensure every case is being attended to.{/ts}</div>
    <div class="clear"></div>
</div>
<crm-angular-js modules="afsearchNYSSCaseloadOverviewTotals">

            <form>
                <afsearch-n-y-s-s-caseload-overview-totals></afsearch-n-y-s-s-caseload-overview-totals>
            </form>

</crm-angular-js>
<crm-angular-js modules="afsearchNYSSCaseloadOverviewDetails">
    <details open class="crm-accordion-light">
        <summary>{ts}District-wide Details{/ts}{help id="id-overview_details"}</summary>
        <div>
            <div afsearchNYSSCaseloadOverviewDetails-test-dir></div>
            <form>
                <afsearch-n-y-s-s-caseload-overview-details></afsearch-n-y-s-s-caseload-overview-details>
            </form>
        </div>
    </details>
</crm-angular-js>
</div>

