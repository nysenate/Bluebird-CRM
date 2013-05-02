<html>
<title>REST API explorer</title>
<style>
{literal}
#result {background:lightgrey;}
#selector a {margin-right:10px;}
.required {font-weight:bold;}
.helpmsg {background:yellow;}
#explorer label {display:inline;}

{/literal}
</style>
<script>
resourceBase = '{$config->resourceBase}';
if (!jQuery) {ldelim}
   var head= document.getElementsByTagName('head')[0];
   var script= document.createElement('script');
   script.type= 'text/javascript';
   script.src= resourceBase + '/packages/jquery/jquery.min.js';
   head.appendChild(script);
{rdelim}

var restURL = '{crmURL p="civicrm/ajax/rest"}';
restURL = restURL.replace("&amp;","&"); // needed for J! fix CRM-10270

if (restURL.indexOf('?') == -1 )
  restURL = restURL + '?';
else
  restURL = restURL + '&';
{literal}
if (typeof $ == "undefined") {
  $ = cj;
}

function toggleField (name,label,type) {
  var h = '<div><label>'+label+'</label><input name='+name+ ' id="'+name+ '" /></div>';
  if ( $('#extra #'+ name).length > 0) {
    $('#extra #'+ name).parent().remove();
  }
  $('#extra').append (h);

}

function buildForm (entity, action) {
  var id = entity+ '_id';
  var h = '<label>'+id+'</label><input id="'+id+ '" size="3" maxlength="20" />';
  if (action == 'delete') {
    $('#extra').html(h);
    return;
  }

  cj().crmAPI (entity,'getFields',{version : 3}
             ,{ success:function (data){
                  h='<i>Available fields (click on it to add it to the query):</i>';
                  $.each(data.values, function(key, value) {
                    name =value.name;
                    if (name == 'id')
                      name = entity+'_id';
                    if (value.title == undefined) {
                      if (value.name == undefined)
                        value.title = value.label;
                      else
                        value.title = value.name;
                    }
                    if (value.required == true) {
                      required = " required";
                    } else {
                      required = "";
                    }
                    h= h + "<a id='"+name+"' class='type_"+ value.type +  required +"'>"+value.title+"</a>";
                  });
                  $('#selector').html(h).find ('a').click (function(){
                    toggleField (this.id,this.innerHTML,this.class);
                  });
                }
              });
}

function generateQuery () {
    var version = 3;

    var entity = $('#entity').val();
    var action = $('#action').val();
    var debug = "";
    if ($('#debug').attr('checked'))
      debug= "debug=1&";
    var sequential = "";
    if ($('#sequential').attr('checked'))
      sequential= "sequential=1&";
    var json = "";
    if ($('#json').attr('checked'))
      json= "json=1&";
    query="";
    if (entity == '') {query= "Choose an entity. "};
    if (action == '') {query=query + "Choose an action.";}
    if (entity == '' || action == '') {
      $('#query').val (query);
      return;
    }
    extra ="";
    $('#extra input').each (function (i) {
      val = $(this).val();
      if (val) {
        extra = extra + "&" +this.id +"="+val;
      }
    });
    query = restURL+json+sequential+debug+'&entity='+entity+'&action='+action+extra;
    $('#query').val (query);
    if (action == 'delete' && $('#selector a').length == 0) {
      buildForm (entity, action);
      return;
    }
    if ( action =='create' && $('#selector a').length == 0) {
      buildForm (entity, action);
      return;
    }
    runQuery (query);
}

function runQuery(query) {
    var vars = [], hash,smarty = '',php = " array (version => \'3\',",json = "{  ", link ="";
    window.location.hash = query;
    $('#result').html('<i>Loading...</i>');
    $.post(query,function(data) {
      $('#result').text(data);
    },'text');
    link="<a href='"+query+"' title='open in a new tab' target='_blank'>ajax query</a>&nbsp;";
    var RESTquery = resourceBase +"/extern/rest.php?"+ query.substring(restURL.length,query.length) + "&key={yoursitekey}&api_key={yourkey}";
    $("#link").html(link+"|<a href='"+RESTquery+"' title='open in a new tab' target='_blank'>REST query</a>.");

    var hashes = query.slice(query.indexOf('?') + 1).split('&');
    for(var i = 0; i < hashes.length; i++) {

        hash = hashes[i].split('=');

        switch (hash[0]) {
           case 'version':
           case 'debug':
           case 'json':
             break;
           case 'action':
             var action= hash[1];
             break;
           case 'entity':
             var entity= hash[1];
             break;
           default:
             if (typeof hash[1] == 'undefined')
               break;
             smarty = smarty+ hash[0] + '="'+hash[1]+ '" ';
             php = php+"'"+ hash[0] +"' =>'"+hash[1]+ "', ";
             json = json+"'"+ hash[0] +"' :'"+hash[1]+ "', ";
        }
    }
    json = json.slice (0,-2) + '}';
    php = php.slice (0,-2) + ')';
    $('#php').html('$results=civicrm_api("'+entity+'","'+action+"\",\n  "+php+');');
    $('#jQuery').html ("cj().crmAPI ('"+entity+"','"+action+"',"+json+"<br>&nbsp;&nbsp;,{ success:function (data){&nbsp;&nbsp;&nbsp;&nbsp;<br> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;cj.each(data, function(key, value) {// do something  });<br> &nbsp;&nbsp;&nbsp;&nbsp;}<br> });");

    if (action == "get") {//using smarty only make sense for get action
      $('#smarty').html('{crmAPI var="'+entity+'S" entity="'+entity+'" action="'+action+'" '+smarty+'}<br>{foreach from=$'+entity+'S.values item='+entity+'}<br/>  &lt;li&gt;{$'+entity+'.example}&lt;/li&gt;<br>{/foreach}');
    } else {
      $('#smarty').html("smarty uses only 'get' actions");
    }
    $('#generated').show();

}

cj(function ($) {
  query=window.location.hash;
  t="#/civicrm/ajax/rest";
  if (query.substring(0, t.length) === t) {
    $('#query').val (query.substring(1)).focus();
  } else {
    window.location.hash="explorer"; //to be sure to display the result under the generated code in the viewport
  }
  $('#entity').change (function() { $("#selector").empty();generateQuery();  });
  $('#action').change (function() { $("#selector").empty();generateQuery();  });
  $('#debug').change (function() { generateQuery();  });
  $('#sequential').change (function() { generateQuery();  });
  $('#json').change (function() { generateQuery();  });
  $('#explorer').submit(function() {runQuery($('#query').val()); return false; });

  $('#extra').live ('change',function () {
    generateQuery();
  });
});
{/literal}
</script>
<body>
<form id="explorer">
<label>entity</label>
<select id="entity">
  <option value="" selected="selected">Choose...</option>
{crmAPI entity="Entity" action="get" var="entities" version=3}
{foreach from=$entities.values item=entity}
  <option value="{$entity}">{$entity}</option>
{/foreach}
</select>
<label>action</label>
<select id="action">
  <option value="" selected="selected">Choose...</option>
  <option value="get">get</option>
  <option value="create">create</option>
  <option value="delete">delete</option>
  <option value="getfields">getfields</option>
  <option value="getactions">getactions</option>
  <option value="getcount">getcount</option>
  <option value="getsingle">getsingle</option>
  <option value="getvalue">getvalue</option>
  <option value="update">update</option>
</select>
<label>debug</label>
<input type="checkbox" id="debug" checked="checked">
<input type="hidden" id="version" name="version" value="3" title="sequential is a more compact format, that is nicer and general and easier to use for json and smarty.">
<label>sequential</label>
<input type="checkbox" id="sequential" checked="checked">
<label>json</label>
<input type="checkbox" id="json" checked="checked">
<br>
<div id="selector"></div>
<div id="extra"></div>
<input size="90" maxsize=300 id="query" value="{crmURL p="civicrm/ajax/rest" q="json=1&debug=on&entity=Contact&action=get&sequential=1&return=display_name,email,phone"}"/>
<input type="submit" value="GO" title="press to run the API query"/>
<table id="generated" border=1 style="display:none;">
<caption>Generated codes for this api call</caption>
<tr><td>URL<td><div id="link"></div></td></tr>
<tr><td>smarty<td><div id="smarty" title='smarty syntax (mostly works for get actions)'></div></td></tr>
<tr><td>php<td><div id="php" title='php syntax'></div></td></tr>
<tr><td>jQuery<td><div id="jQuery" title='jQuery syntax'></div></td></tr>
</table>
<pre id="result">
You can choose an entity and an action (eg Tag Get to retrieve a list of the tags)
Or your can directly modify the url in the field above and press enter.

When you use the create method, it displays the list of existing fields for this entity.
click on the name of the fields you want to populate, fill the value(s) and press enter

The result of the ajax calls are displayed in this grey area.
</pre>
</body>
</html>
