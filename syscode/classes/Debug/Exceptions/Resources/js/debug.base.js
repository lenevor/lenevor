(function(d)
{
    /* IE8 Incompatibility crap */
    var elements = ['section', 'header', 'footer'];

    for (let i = 0; i < elements.length; i++)
    {
        d.createElement(elements[i]);
    }

    var previousFrame = null;
    var previousInfo  = null;
    var allFrames     = d.querySelectorAll('.frame');
    var allFramesCode = d.querySelectorAll('.code-source');
    let evento        = ((document.ontouchstart !== null) ? 'mouseup' : 'touchstart');

    function changeTo(el) 
    {
        if (previousInfo) previousInfo.style.display = "none";

        previousInfo = el;

        el.style.display = "block";
    }

    function selectFrameInfo(index)
    {
        var el = allFramesCode[index];

        if (el)
        {
            if (el.closest('[data-frame]'))
            {
                return changeTo(el);
            }
        }
    }

    for (var i = 0; i < allFrames.length; i++)
    {
        (function(i)
        {
            var el = allFrames[i];

            el.addEventListener(evento, (e) =>
            {
                e.preventDefault();
                
                if (previousFrame)
                {
                    previousFrame.classList.remove("active");
                    el.classList.add("active"); 
                }
                
                el.classList.add("active");  
                      
                previousFrame = el;
                                
                selectFrameInfo(el.attributes["data-index"].value);
            });

        })(i);
    }

})(document);