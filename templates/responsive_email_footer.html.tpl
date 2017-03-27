                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>

<!-- footer -->
        <table style="min-width:320px;" width="100%" cellspacing="0" cellpadding="0" bgcolor="#efefef">
          <tr>
            <td class="two-column" style="padding:29px 0 0;text-align:center;font-size:0;">
              <!--[if (gte mso 9)|(IE)]>
              <table width="100%" cellpadding="0" cellspacing="0">
              <tr>
{{foreach from=$senator.offices item=offinfo name=offices}}
              <td width="{{math equation="100/x" x=$smarty.foreach.offices.total format="%d"}}%" valign="top" style="padding:0;">
              <![endif]-->
              <div class="column" style="width:100%;max-width:285px;display:inline-block;vertical-align:top;">
                <table width="100%" style="width:100%;max-width:285px;display:inline-block;vertical-align:top;" cellpadding="0" cellspacing="0">
                  <tr>
                    <td style="padding:0 10px 20px;">
                      <table width="100%" style="min-width:265px;" cellpadding="0" cellspacing="0" bgcolor="#ffffff">
                        <tr>
                          <td style="padding:24px 15px 33px;" bgcolor="#ffffff">
                            <table width="100%" cellpadding="0" cellspacing="0">
                              <tr>
                                <td align="center" style="font:bold 14px/16px Helvetica, Arial, sans-serif; color:#000; letter-spacing:-0.2px; padding:0 0 20px;">
                                  <a href="https://www.google.com/maps/@{{$offinfo->latitude}},{{$offinfo->longitude}},17z" target="_blank" style="color:#010101; text-decoration:underline;">{{$offinfo->name}}</a>
                                </td>
                              </tr>
                              <tr>
                                <td class="address" align="center" style="font:12px/14px Helvetica, Arial, sans-serif; color:#000;">
                                  {{$offinfo->street}}<br/>
                                  {{$offinfo->city}}, {{$offinfo->province}} {{$offinfo->postal_code}}<br/>
                                  Phone: <a href="tel:{{$offinfo->phone}}" target="_blank" style="color:#010101; text-decoration:none;">{{$offinfo->phone}}</a><br/>
{{if $offinfo->fax}}
                                  Fax: <a href="tel:{{$offinfo->fax}}" target="_blank" style="color:#010101; text-decoration:none;">{{$offinfo->fax}}</a><br/>
{{/if}}
                                </td>
                              </tr>
                            </table>
                          </td>
                        </tr>
                      </table>
                    </td>
                  </tr>
                </table>
              </div>
              <!--[if (gte mso 9)|(IE)]>
              </td>
{{/foreach}}
              </tr>
              </table>
              <![endif]-->
            </td>
          </tr>
          <tr>
            <td>
              <table cellpadding="0" cellspacing="0" align="center" style="margin:0 auto !important;">
                <tr>
                  <td align="center" style="padding:0 0 19px;">
                    <a target="_blank" href="https://www.nysenate.gov/"><img src="{{$bbcfg.email_images_common_base_url}}/nyss_seal_bw.png" border="0" style="vertical-align:top; width:115px; height:116px;" width="115" height="116" alt="New York State Senate" /></a>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        <!--[if (gte mso 9)|(IE)]>
        </td>
        </tr>
        </table>
        <![endif]-->
      </div>
    </center>
  </body>
</html>
