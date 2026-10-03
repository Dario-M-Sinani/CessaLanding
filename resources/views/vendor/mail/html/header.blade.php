@props(['url'])
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ asset('img/Logo_CESSA_240x240.png') }}" class="logo" alt="{{ trim($slot) }}">
</a>
</td>
</tr>
