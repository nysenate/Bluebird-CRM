{if !$printOnly }
{include file="CRM/Report/Form.tpl"}
{else}

{if $groupRows}
    {assign var=pageNum value=1}
    {foreach from=$groupRows item=groupData key=group} 
    	{if $pageNum eq 1}
	    <div>
	{else}
	    <div class="page">
	{/if}
       	<table class="head">
        
        <tr><td style="text-align: left;">Organization Name: {$groupData.org}</td>
            <td style="text-align: center;"></td> 
            <td style="text-align: right;">Odd/Even: {$groupData.odd}</td>
        </tr>
        <tr>
          <td style="text-align: left;">Street Name: {$groupData.street_name}</td>
          <td style="text-align: center;">Walk List</td> 
          <td style="text-align: right;">&nbsp;</td>
        </tr>
        <tr>
          <td style="text-align: left;">City-Zip: {$groupData.city_zip}</td>
          <td style="text-align: center;">{$groupInfo.date}{$groupInfo.descr}
	      {if $statistics }
   	          {foreach from=$statistics.filters item=row}
                      <br /> {$row.title} &nbsp:{$row.value}
                  {/foreach}
              {/if}
	  </td> 
          <td style="text-align: right;">&nbsp;</td>
        </tr>
	       
	</table>
	{if $pdfRows.$group}
	    <table class="body">
	    <thead class="sticky">
	    <tr>
	    {foreach from=$pdfHeaders item=header}
	        <th {$header.class}  class='reports-header-right' >{$header.title}</th> 	          
	    {/foreach}
            </tr>
	    </thead>
	    <tbody>
	    {foreach from=$pdfRows.$group item=row}
	        <tr>
	        {foreach from=$pdfHeaders item=title key=k}
		    {if $k eq 'rcode'}
                        <td>Q1&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D<br />
                            Q2&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D<br />
                            Q3&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D<br />
                            Q4&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D
                        </td>
                    {elseif $k eq 'status'} 
                        <td>NH&nbsp;MV&nbsp;D&nbsp;WA</td>
		    {else}
		        <td>{$row.$k}</td>
                    {/if}			 
	        {/foreach}
		</tr>
	    {/foreach}
	    </tr>
	    <tbody>
	    </table>
	{/if}    
	
        <p>Response Codes: Y= Yes; N= No; U= Undecided; D= Declined to State</p>
        <p>Status Codes: NH= Not Home; MV= Moved; D= Deceased; WA= Wrong Address</p>
        <p>VH (Voting History): A=Always; O=Occasional; N=New</p>
	<p style="text-align: right;">Page {$pageNum} of {$pageTotal}</p>
	{assign var=pageNum value=`$pageNum+1`}
	<br />             	
	</div>
     {/foreach}
{/if}
{/if}=======
{if !$printOnly }
{include file="CRM/Report/Form.tpl"}
{else}

{if $groupRows}
    {foreach from=$groupRows item=groupData key=group} 
        {if $group eq 1 } 
       	    <table class="head page">
        {else}
            <table class="head">
        {/if}
 

     	      
        <tr><td style="text-align: left;">Organization Name: {$groupData.org}</td>
            <td style="text-align: center;"></td> 
            <td style="text-align: right;">Odd/Even: {$groupData.odd}</td>
        </tr>
        <tr>
          <td style="text-align: left;">Street Name: {$groupData.street_name}</td>
          <td style="text-align: center;">Walk List</td> 
          <td style="text-align: right;">&nbsp;</td>
        </tr>
        <tr>
          <td style="text-align: left;">City-Zip: {$groupData.city_zip}</td>
          <td style="text-align: center;">{$groupInfo.date}{$groupInfo.descr}</td> 
          <td style="text-align: right;">&nbsp;</td>
        </tr>
	       
	</table>
	{if $pdfRows.$group}
	    <table class="body">
	    <tr>
	    {foreach from=$pdfHeaders item=title}
	        <th>{$title}</th> 	          
	    {/foreach}
            </tr>
	    {foreach from=$pdfRows.$group item=row}
	        <tr>
	        {foreach from=$pdfHeaders item=title key=k}
		    {if $k eq 'rcode'}
                        <td>Q1&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D<br>
                            Q2&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D<br>
                            Q3&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D<br>
                            Q4&nbsp;&nbsp;&nbsp;&nbsp;Y&nbsp;&nbsp;&nbsp;&nbsp;N&nbsp;&nbsp;&nbsp;&nbsp;U&nbsp;&nbsp;&nbsp;&nbsp;D
                        </td>
                    {elseif $k eq 'status'} 
                        <td>NH&nbsp;MV&nbsp;D&nbsp;WN</td>
		    {else}
		        <td>{$row.$k}</td>
                    {/if}			 
	        {/foreach}
		</tr>
	    {/foreach}
	    </tr>
	    </table>
	{/if}    
	
        <p>Response Codes: Y= Yes; N= No; U= Undecided; D= Declined to State</p>
        <p>Status Codes: NH= Not Home; MV= Moved; D= Deceased; WA= Wrong Address</p>
        <p>VH (Voting History): A=Always; O=Occasional; N=New</p>
	<br/>             	

     {/foreach}
{/if}
{/if}
