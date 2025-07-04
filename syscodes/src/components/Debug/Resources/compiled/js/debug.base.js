(function(d, w) {
    /** 
     * CODE FOR SELECTION OF FRAMES 
     */

    /* IE8 Incompatibility crap */
    let elements = ['section', 'header', 'nav', 'footer'];

    for (let i = 0; i < elements.length; i++) {
        d.createElement(elements[i]);
    }

    /**
     * CODE FOR CONTROL OF ELEMENTS HTML IN THE HEADER
     */

    let header = d.querySelector('header');
    let message = d.querySelector('.time');

    message.style = "display: none";
    
    w.addEventListener('scroll', (e) => {
        if (d.documentElement.scrollTop > 10) {
            /* Access to the attribute data-theme */
            if (d.documentElement.dataset.theme == 'dark') {
                header.style.background = '#1F2937';
                header.style.borderBottom = '1px solid rgba(36, 68, 86, 0.5)';
                header.style.boxShadow = '0 0 15px 4px rgba(0, 0, 0, 0.2)';
            } else {
                header.style.background = '#F3F4F6';
                header.style.borderBottom = 'none';
                header.style.boxShadow = '0 0 15px 4px rgba(0, 0, 0, 0.2)';
            }
        } else {
            if (d.documentElement.dataset.theme == 'dark') {
                header.style.background = '#111827';
                header.style.borderBottom = '1px solid rgba(31, 41, 51, 1)';
                header.style.boxShadow = 'none';
            } else {
                header.style.background = '#EAE9F1';
                header.style.borderBottom = 'none';
                header.style.boxShadow = 'none';
            }
        }

        if (d.documentElement.scrollTop > 150) {
            message.style = "display: block";
        } else {
            message.style = "display: none";
        }
    });

    /**
     * MENU SLIDER
     */

    let menu_1 = d.querySelector('.space');
    let sections = d.querySelectorAll('.section');
    let evento   = ((d.ontouchstart !== null) ? 'mouseup' : 'touchstart');
    let indexSectionActive;

    const observer = new IntersectionObserver((tickets, observer) => {
        tickets.forEach(ticket => {
            if (ticket.isIntersecting) {
                indexSectionActive = [...sections].indexOf(ticket.target);
                
            }
        });
    }, {
        rootMargin : '-80px 80px 0px 0px',
        threshold : 0.3
    });

    sections.forEach(section => observer.observe(section));

    /**
     * DROPDOWN COMPONENT
     */

    let dropdown = d.getElementById('menuDropdown');
    let menu_2   = d.querySelector('nav:nth-child(2) a');

    /* Show|hide dropdown */
    menu_2.addEventListener(evento, function (e) {
        /* Prevents the click from propagating to the modal */
        e.stopPropagation(0);

        dropdown.classList.toggle("active"); 
    });

    /* Hide dropdown on click outside */
    w.addEventListener(evento, function (e) {
        if ( ! dropdown.contains(e.target)) {            
            dropdown.classList.remove("active");
        }
    });
    
    /**
     * CODE FOR SELECTED THE FRAMES
     */

    var previousFrame = null;
    var previousInfo  = null;
    var allFrames     = d.querySelectorAll('.frame');
    var allFramesCode = d.querySelectorAll('.code-source');

    function changeTo(el) 
    {
        if (previousInfo) previousInfo.classList.remove("active");

        previousInfo = el;

        el.classList.add("active");
    }

    function selectFrameInfo(index)
    {
        var el = allFramesCode[index];

        if (el) {
            if (el.closest('[data-frame]')) {
                return changeTo(el);
            }
        }
    }

    for (let i = 0; i < allFrames.length; i++) {
        (function(i, el) {
            var el = allFrames[i];

            el.addEventListener(evento, (e) => {
                e.preventDefault();

                allFrames[0].classList.remove("active");
                allFramesCode[0].classList.remove("active");
                
                if (previousFrame) {
                    previousFrame.classList.remove("active");                    
                }
                
                el.classList.add("active");  
                      
                previousFrame = el;
                                
                selectFrameInfo(el.attributes["data-index"].value);
            });

        })(i);
    }

})(document, window);