<footer>

	<div class="info">

		<div class="name">
			<span class="status-code" title="<?= e(__('exception.statusCode'))?>"><?= $debug->sendHttpCode() ?></span>
			<span class="benchmark-time" title="<?= e(__('exception.benchmark'))?>">
				<svg id="cronometro" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 512 512" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" xmlns:xlink="http://www.w3.org/1999/xlink">
					<g>
						<path d="M303 62l0 -37c0,-3 -2,-5 -5,-5l-96 0c-3,0 -5,2 -5,5l0 37c-27,7 -52,19 -73,35l-7 -7c-2,-2 -2,-5 0,-7l2 -2c2,-2 2,-5 0,-7l-16 -17c-2,-2 -6,-2 -8,0l-43 43c-2,2 -2,6 0,8l17 16c2,2 5,2 7,0l2 -2c2,-2 5,-2 7,0l6 6c-33,37 -53,86 -53,140 0,117 95,212 212,212 117,0 212,-95 212,-212 0,-99 -67,-182 -159,-206zm-218 195l47 0c4,0 7,3 7,7 0,5 -3,8 -7,8l-47 0c3,86 70,155 156,160l0 -49c0,-4 3,-8 7,-8 4,0 8,4 8,8l0 49c86,-3 155,-72 159,-158l-49 0c-4,0 -7,-3 -7,-8 0,-4 3,-7 7,-7l49 0c-4,-85 -73,-153 -157,-157l0 46c0,4 -4,8 -8,8 -4,0 -7,-4 -7,-8l0 -46c-85,4 -153,71 -158,155zm120 3c-3,0 -4,-3 -4,-6 1,-3 4,-4 7,-4l25 7c0,-1 1,-1 1,-2l-40 -70c-2,-3 -1,-8 3,-10 4,-2 8,-1 10,3l40 70c1,0 2,-1 3,-1 11,0 20,9 20,20l96 26c3,1 4,3 4,6 -1,3 -4,4 -7,4l-96 -26c0,1 -1,2 -1,2l8 15c2,3 1,8 -3,10 -3,2 -8,1 -10,-3l-8 -14c-1,0 -2,0 -3,0 -11,0 -20,-9 -20,-20l0 0 -25 -7z"/>
					</g>
				</svg>
				<span>{elapsed_time}</span>
			</span>
			<span class="memory" title="<?= e(__('exception.memoryUsage'))?>">
				<svg id="chipset" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 512 512" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" xmlns:xlink="http://www.w3.org/1999/xlink">
					<defs>
						<style type="text/css">
						<![CDATA[
							.fil1 {fill:#303030}
							.fil2 {fill:#A5A5A5}
							.fil3 {fill:#B5B5B5}
							.fil4 {fill:#C5C5C5;fill-rule:nonzero}
							.fil5 {fill:#000000}
						]]>
						</style>
					</defs>
					<g>
						<path d="M407 111l32 0 0 21 -32 0 0 -21zm0 52l32 0 0 21 -32 0 0 -21zm0 53l32 0 0 21 -32 0 0 -21zm0 52l32 0 0 21 -32 0 0 -21zm0 53l32 0 0 20 -32 0 0 -20zm0 52l32 0 0 21 -32 0 0 -21zm-295 -280l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm53 0l0 -32 20 0 0 32 -20 0zm-279 296l-32 0 0 -21 32 0 0 21zm0 -53l-32 0 0 -21 32 0 0 21zm0 -52l-32 0 0 -21 32 0 0 21zm0 -52l-32 0 0 -21 32 0 0 21zm0 -53l-32 0 0 -21 32 0 0 21zm0 -52l-32 0 0 -21 32 0 0 21zm294 279l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-53 0l0 32 -21 0 0 -32 21 0z"/>
						<rect class="fil1" x="83" y="82" width="334" height="335" rx="26" ry="26"/>
						<rect class="fil2" x="111" y="111" width="277" height="278" rx="10" ry="10"/>
						<path class="fil3" d="M122 111l256 0c6,0 11,5 11,10l0 39c-38,64 -130,107 -186,107 -56,0 -64,-8 -92,-21l0 -125c0,-5 5,-10 11,-10z"/>
						<path class="fil4" d="M152 288l0 8 -15 0 0 -8 15 0zm114 0l-12 0 0 8 12 0 0 -8zm-20 0l0 8 -15 0 0 -8 15 0zm-23 0l0 8 -16 0 0 -8 16 0zm-24 0l0 8 -15 0 0 -8 15 0zm-23 0l0 8 -16 0 0 -8 16 0zm48 34l0 -8 78 0 0 8 -78 0zm-86 0l0 -8 78 0 0 8 -78 0zm38 18l0 8 -39 0 0 -8 39 0zm141 0l0 8 -8 0 0 -8 8 0zm-31 0l0 8 -40 0 0 -8 40 0zm-63 0l0 8 -8 0 0 -8 8 0zm-16 0l0 8 -8 0 0 -8 8 0z"/>
						<circle class="fil5" cx="159" cy="161" r="17"/>
						<path d="M407 111l74 0 0 11 -74 0 0 -11zm0 52l74 0 0 11 -74 0 0 -11zm0 53l74 0 0 10 -74 0 0 -10zm0 52l74 0 0 11 -74 0 0 -11zm0 53l74 0 0 10 -74 0 0 -10zm0 52l74 0 0 10 -74 0 0 -10zm-313 16l-73 0 0 -11 73 0 0 11zm0 -53l-73 0 0 -10 73 0 0 10zm0 -52l-73 0 0 -11 73 0 0 11zm0 -52l-73 0 0 -11 73 0 0 11zm0 -53l-73 0 0 -10 73 0 0 10zm0 -52l-73 0 0 -11 73 0 0 11zm18 -34l0 -73 10 0 0 73 -10 0zm52 0l0 -73 10 0 0 73 -10 0zm52 0l0 -73 10 0 0 73 -10 0zm52 0l0 -73 11 0 0 73 -11 0zm52 0l0 -73 11 0 0 73 -11 0zm53 0l0 -73 10 0 0 73 -10 0zm15 313l0 74 -10 0 0 -74 10 0zm-52 0l0 74 -10 0 0 -74 10 0zm-52 0l0 74 -11 0 0 -74 11 0zm-52 0l0 74 -11 0 0 -74 11 0zm-52 0l0 74 -11 0 0 -74 11 0zm-53 0l0 74 -10 0 0 -74 10 0z"/>
					</g>
				</svg>
				<span>MU: <b><?= $template->displayMemory(memory_get_usage(true)) ?></b></span>
			</span>		
			<span class="memory" title="<?= e(__('exception.maxMemoryUsage'))?>">
				<svg id="chipset" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 512 512" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" xmlns:xlink="http://www.w3.org/1999/xlink">
					<defs>
						<style type="text/css">
						<![CDATA[
							.fil1 {fill:#303030}
							.fil2 {fill:#B5B5B5}
							.fil3 {fill:#D5D5D5}
							.fil4 {fill:#EBEBEB;fill-rule:nonzero}
							.fil5 {fill:#000000}
						]]>
						</style>
					</defs>
					<g>
						<path d="M407 111l32 0 0 21 -32 0 0 -21zm0 52l32 0 0 21 -32 0 0 -21zm0 53l32 0 0 21 -32 0 0 -21zm0 52l32 0 0 21 -32 0 0 -21zm0 53l32 0 0 20 -32 0 0 -20zm0 52l32 0 0 21 -32 0 0 -21zm-295 -280l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm52 0l0 -32 21 0 0 32 -21 0zm53 0l0 -32 20 0 0 32 -20 0zm-279 296l-32 0 0 -21 32 0 0 21zm0 -53l-32 0 0 -21 32 0 0 21zm0 -52l-32 0 0 -21 32 0 0 21zm0 -52l-32 0 0 -21 32 0 0 21zm0 -53l-32 0 0 -21 32 0 0 21zm0 -52l-32 0 0 -21 32 0 0 21zm294 279l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-52 0l0 32 -21 0 0 -32 21 0zm-53 0l0 32 -21 0 0 -32 21 0z"/>
						<rect class="fil1" x="83" y="82" width="334" height="335" rx="26" ry="26"/>
						<rect class="fil2" x="111" y="111" width="277" height="278" rx="10" ry="10"/>
						<path class="fil3" d="M122 111l256 0c6,0 11,5 11,10l0 39c-38,64 -130,107 -186,107 -56,0 -64,-8 -92,-21l0 -125c0,-5 5,-10 11,-10z"/>
						<path class="fil4" d="M152 288l0 8 -15 0 0 -8 15 0zm114 0l-12 0 0 8 12 0 0 -8zm-20 0l0 8 -15 0 0 -8 15 0zm-23 0l0 8 -16 0 0 -8 16 0zm-24 0l0 8 -15 0 0 -8 15 0zm-23 0l0 8 -16 0 0 -8 16 0zm48 34l0 -8 78 0 0 8 -78 0zm-86 0l0 -8 78 0 0 8 -78 0zm38 18l0 8 -39 0 0 -8 39 0zm141 0l0 8 -8 0 0 -8 8 0zm-31 0l0 8 -40 0 0 -8 40 0zm-63 0l0 8 -8 0 0 -8 8 0zm-16 0l0 8 -8 0 0 -8 8 0z"/>
						<circle class="fil5" cx="159" cy="161" r="17"/>
						<path d="M407 111l74 0 0 11 -74 0 0 -11zm0 52l74 0 0 11 -74 0 0 -11zm0 53l74 0 0 10 -74 0 0 -10zm0 52l74 0 0 11 -74 0 0 -11zm0 53l74 0 0 10 -74 0 0 -10zm0 52l74 0 0 10 -74 0 0 -10zm-313 16l-73 0 0 -11 73 0 0 11zm0 -53l-73 0 0 -10 73 0 0 10zm0 -52l-73 0 0 -11 73 0 0 11zm0 -52l-73 0 0 -11 73 0 0 11zm0 -53l-73 0 0 -10 73 0 0 10zm0 -52l-73 0 0 -11 73 0 0 11zm18 -34l0 -73 10 0 0 73 -10 0zm52 0l0 -73 10 0 0 73 -10 0zm52 0l0 -73 10 0 0 73 -10 0zm52 0l0 -73 11 0 0 73 -11 0zm52 0l0 -73 11 0 0 73 -11 0zm53 0l0 -73 10 0 0 73 -10 0zm15 313l0 74 -10 0 0 -74 10 0zm-52 0l0 74 -10 0 0 -74 10 0zm-52 0l0 74 -11 0 0 -74 11 0zm-52 0l0 74 -11 0 0 -74 11 0zm-52 0l0 74 -11 0 0 -74 11 0zm-53 0l0 74 -10 0 0 -74 10 0z"/>
					</g>
				</svg>
				<span>PMU: <b><?= $template->displayMemory(memory_get_peak_usage(true)) ?></b></span>
			</span>	
			<span class="memory" title="<?= e(__('exception.memoryLimit'))?>">
				<svg id="chipset" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 512 512" version="1.1" style="shape-rendering:geometricPrecision; text-rendering:geometricPrecision; image-rendering:optimizeQuality; fill-rule:evenodd; clip-rule:evenodd" xmlns:xlink="http://www.w3.org/1999/xlink">
					<defs>
						<style type="text/css">
						<![CDATA[
							.fil1 {fill:none}
							.str1 {stroke:#B5B5B5;stroke-width:25;stroke-linecap:round}
							.str2 {stroke:#A5A5A5;stroke-width:30}
							.str3 {stroke:#E5E5E5;stroke-width:20;stroke-linecap:round}
						]]>
						</style>
					</defs>
					<g>
						<path class="fil1 str1" d="M155 98l0 -48m47 48l0 -48m48 48l0 -48m48 48l0 -48m47 48l0 -48m-190 400l0 -48m47 48l0 -48m48 48l0 -48m48 48l0 -48m47 48l0 -48m57 -247l48 0m-48 47l48 0m-48 48l48 0m-48 48l48 0m-48 47l48 0m-400 -190l48 0m-48 47l48 0m-48 48l48 0m-48 48l48 0m-48 47l48 0"/>
						<rect class="fil1 str2" x="98" y="98" width="305" height="305" rx="24" ry="24"/>
						<path class="fil1 str3" d="M155 345l95 0m-95 -33l143 0"/>
					</g>
				</svg>
				<span><?= ini_get('memory_limit') ?></span>
			</span>							
		</div>
		
		<div class="copy">
			<?= e($handler->getBrand()) ?>
			<span class="version" title="<?= e(__('exception.version'))?>"><?= \Syscodes\Components\Version::RELEASE ?></span>
		</div>

	</div>

</footer>