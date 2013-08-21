<!-- MODULE Block advertising -->
<div class="advertising_block grid_9">
	{foreach from=$webcontents item=webcontent}
	<div class="ad grid_3">
		<div class="visuel">
			<a href="{$webcontent.lien}" title="{$webcontent.titre}">
				<img src="{$image_path}{$webcontent.image}" alt="{$webcontent.titre}" title="{$webcontent.titre}" width="100"  height="150" />
			</a>
		</div>
		<div class="descriptif">
			<h2 style="text-transform: uppercase; ">{$webcontent.titre}</h2>
			<p>{$webcontent.description}</p>
			{if $webcontent.template eq 'product'}
			<div>
				<a class="button" href="{$webcontent.lien}" title="Commander">Commander</a>
			</div>
			{/if}
		</div>	
	</div>
	{/foreach}
</div>
<!-- /MODULE Block advertising -->
