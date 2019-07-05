<div class="alert alert-info" role="alert">
  You can use our client software to connect (Windows, Mac, iOS, Android) or use one of our server config files!
</div>

<h3>Bandwidth Usage</h3>
<hr>
<div class="progress">
  <div class="progress-bar" role="progressbar" aria-valuenow="{{$percentage}}" aria-valuemin="0" aria-valuemax="100" style="min-width: 15em; width: {{$percentage}}%;">
    {{$bandwidthUsage}} / {{$bandwidthLimit}}
  </div>
</div>
<br>
<h3>Connection Parameters</h3>
<br>
<table class="table">
	<tbody>
	  <tr>
	    <td>Hostname:</td>
	    <td>Choose a hostname based on region below</td>
	  </tr>
	  <tr>
	    <td>Username:</td>
	    <td>{{$username}}</td>
	  </tr>
	  <tr>
	    <td>Password:</td>
	    <td><span id="password">•••••••••</span> <i id="reveal" class="glyphicon glyphicon-eye-open"></i></td>
	  </tr>
	  <tr>
	    <td>External ID (IKEv2 only):</td>
	    <td><code>privacyservers.nl</code></td>
	  </tr>
	  <tr>
	    <td>Pre-Shared Key (L2TP only):</td>
	    <td><code>privacyservers</code></td>
	  </tr>
	</tbody>  
</table>
<br>
<h3>Server List</h3>
<hr>
<table class="table">
    <thead>
        <tr>
            <th scope="col"></th>
            <th scope="col">Hostname</th>
            <th scope="col">Location</th>
            <th scope="col">Protocol</th>
            <th scope="col"></th>
        </tr>
    </thead>
    <tbody>
        {foreach from=$regions["locations"] item=region}

        <tr>
            <td><img height="24" src="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/3.3.0/flags/4x3/{{$region->iso|lower}}.svg" /></td>
            <td>{{$region->name}}.privacyservers.nl</td>
            <td>{{$region->city}}, {{$region->country}}</td>
            <td><span class="label label-success">OpenVPN</span> <span class="label label-success">IKEv2</span> <span class="label label-success">L2TP</span></td>
            <td>
                <!-- Single button -->
                <div class="btn-group">
                    <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Download OpenVPN <span class="caret"></span>
                    </button>
                    <ul class="dropdown-menu">
	                    {foreach from=$region->endpoints->openvpn item=openvpn}
                        	<li><a href="{{$openvpn->url}}">{{$openvpn->name}}</a></li>
                        {/foreach}	
                    </ul>
                </div>
            </td>
        </tr>
        {/foreach}
    </tbody>
</table>

<script>
var revealed = false;
$( "#reveal" ).on( "click", function() {
	if(revealed)
	{
		$("#password").html("•••••••••");
		revealed = false;
	}
	else
	{
		$("#password").html("{{$password}}");
		revealed = true;	
	}
});	
</script>