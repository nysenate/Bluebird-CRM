<div class="crm-block crm-content-block">
  <div class="crm-section">
  <div id="ContactTypes">
    <h3>Contact Counts</h3>
    <table>
      <tr>{foreach from=$contactTypes key=type item=tcount}<th>{$type}</th>{/foreach}
        <th>Male</th>
        <th>Female</th>
        <th>Other Gender</th>
      </tr>
      <tr>{foreach from=$contactTypes key=type item=tcount}<td>{$tcount}</td>{/foreach}
        <td>{$contactGenders.2}</td>
        <td>{$contactGenders.1}</td>
        <td>{$contactGenders.4}</td>
      </tr>
    </table>

    <h3>Email Counts</h3>
    <table>
      <tr>{foreach from=$emailCounts key=type item=tcount}<th>{$type}</th>{/foreach}</tr>
      <tr>{foreach from=$emailCounts key=type item=tcount}<td>{$tcount}</td>{/foreach}</tr>
    </table>

    <h3>Miscellaneous Counts</h3>
    <table>
      <tr>{foreach from=$miscCounts key=type item=tcount}<th>{$type}</th>{/foreach}</tr>
      <tr>{foreach from=$miscCounts key=type item=tcount}<td>{$tcount}</td>{/foreach}</tr>
    </table>
  </div>

  <div id="help">All district counts are based on the contact's primary address only. Issue Code/Keyword/Legislative Position counts are for contact records only (not tags attached to activities or cases). Expand each panel to view the statistics. Calculations are real-time.</div>

  <table>
    <tr id="districts">
      <td width="25%">
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-SenateDistricts-accordion collapsed">
          <div class="crm-accordion-header" id="SenateDistricts">
            Senate Districts
          </div>
          <div class="crm-accordion-body SenateDistricts">
            <table>
              {foreach from=$contactSD key=sd item=sdcount}
                <tr><th>{$sd}</th><td>{$sdcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-AssemblyDistricts-accordion collapsed">
          <div class="crm-accordion-header" id="AssemblyDistricts">
            Assembly Districts
          </div>
          <div class="crm-accordion-body AssemblyDistricts">
            <table>
              {foreach from=$contactAD key=ad item=adcount}
                <tr><th>{$ad}</th><td>{$adcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
      </td>

      <td width="25%">
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-CongressionalDistricts-accordion collapsed">
          <div class="crm-accordion-header" id="CongressionalDistricts">
            Congressional Districts
          </div>
          <div class="crm-accordion-body CongressionalDistricts">
            <table>
              {foreach from=$contactCD key=cd item=cdcount}
                <tr><th>{$cd}</th><td>{$cdcount}</td></tr>
                {foreachelse}
                <tr><td>There is currently no congressional district information in your database.</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-ElectionDistricts-accordion collapsed">
          <div class="crm-accordion-header" id="ElectionDistricts">
            Election Districts
          </div>
          <div class="crm-accordion-body ElectionDistricts">
            <table>
              {foreach from=$contactED key=ed item=edcount}
                <tr><th>{$ed}</th><td>{$edcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-TownADED-accordion collapsed">
          <div class="crm-accordion-header" id="TownADED">
            Town/Assembly/Election Districts
          </div>
          <div class="crm-accordion-body ElectionDistricts">
            <table>
              <tr><td>Town</td><td>AD</td><td>ED</td><td>Count</td></tr>
              {foreach from=$contactTownADED item=tae}
                <tr><th>{$tae.town}</th><th>{$tae.ad}</th><th>{$tae.ed}</th><td>{$tae.count}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
      </td>

      <td width="25%">
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-Counties-accordion collapsed">
          <div class="crm-accordion-header" id="Counties">
            Counties
          </div>
          <div class="crm-accordion-body Counties">
            <table>
              {foreach from=$contactCounty key=county item=countycount}
                <tr><th>{$county}</th><td>{$countycount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-Towns-accordion collapsed">
          <div class="crm-accordion-header" id="Towns">
            Towns
          </div>
          <div class="crm-accordion-body Towns">
            <table>
              {foreach from=$contactTown key=town item=towncount}
                <tr><th>{$town}</th><td>{$towncount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-Wards-accordion collapsed">
          <div class="crm-accordion-header" id="Wards">
            Wards
          </div>
          <div class="crm-accordion-body Wards">
            <table>
              {foreach from=$contactWard key=ward item=wardcount}
                <tr><th>{$ward}</th><td>{$wardcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-Schools-accordion collapsed">
          <div class="crm-accordion-header" id="Schools">
            School Districts
          </div>
          <div class="crm-accordion-body Schools">
            <table>
              {foreach from=$contactSC key=school item=schoolcount}
                <tr><th>{$school}</th><td>{$schoolcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-Zip-accordion collapsed">
          <div class="crm-accordion-header" id="Zip">
            Zip Codes
          </div>
          <div class="crm-accordion-body Zip">
            <table>
              {foreach from=$contactZip key=zip item=zipcount}
                <tr><th>{$zip}</th><td>{$zipcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
      </td>

      <td width="25%">
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-IssueCodes-accordion collapsed">
          <div class="crm-accordion-header" id="IssueCodes">
            Issue Codes
          </div>
          <div class="crm-accordion-body IssueCodes">
            <table>
              {foreach from=$issueCodes key=ic item=iccount}
                <tr><th>{$ic}</th><td>{$iccount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>

        <div class="crm-accordion-wrapper crm-ajax-accordion crm-Keywords-accordion collapsed">
          <div class="crm-accordion-header" id="Keywords">
            Keywords (contacts)
          </div>
          <div class="crm-accordion-body Keywords">
            <table>
              {foreach from=$keywords key=k item=kcount}
                <tr><th>{$k}</th><td>{$kcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-aKeywords-accordion collapsed">
          <div class="crm-accordion-header" id="aKeywords">
            Keywords (activities)
          </div>
          <div class="crm-accordion-body aKeywords">
            <table>
              {foreach from=$akeywords key=k item=akcount}
                <tr><th>{$k}</th><td>{$akcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
        <div class="crm-accordion-wrapper crm-ajax-accordion crm-cKeywords-accordion collapsed">
          <div class="crm-accordion-header" id="cKeywords">
            Keywords (cases)
          </div>
          <div class="crm-accordion-body cKeywords">
            <table>
              {foreach from=$ckeywords key=k item=ckcount}
                <tr><th>{$k}</th><td>{$ckcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>

        <div class="crm-accordion-wrapper crm-ajax-accordion crm-Positions-accordion collapsed">
          <div class="crm-accordion-header" id="Positions">
            Legislative Positions
          </div>
          <div class="crm-accordion-body Positions">
            <table>
              {foreach from=$positions key=p item=pcount}
                <tr><th>{$p}</th><td>{$pcount}</td></tr>
              {/foreach}
            </table>
          </div>
        </div>
      </td>

    </tr> <!--end districts-->
  </table>
  </div>
</div>
