(function(d)
{
	
	let toggle  = d.getElementById('js-menu'),
	    open    = d.querySelector('.menu'),
	    content = d.querySelector('.content'),
	    nav     = d.querySelector('nav'),
	    evento  = ((document.ontouchstart !== null) ? 'mouseup' : 'touchstart');	

	toggle.addEventListener(evento, () => {
		content.classList.toggle('show');
		nav.classList.toggle('show-1');
		open.classList.toggle('open');
	});	

})(document);