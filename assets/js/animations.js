/**
 * Custom Animations & Interactions
 * specifically handling the micro-interactions requested in the prompt
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // Add hover lift effect to all cards dynamically
    const cards = document.querySelectorAll('.card-premium');
    cards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-5px)';
            card.style.boxShadow = 'var(--shadow-lg)';
        });
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0)';
            card.style.boxShadow = 'var(--shadow-sm)';
        });
    });

    // Image Zoom on Hover (for profile pictures or task attachments)
    const zoomImages = document.querySelectorAll('.zoom-hover');
    zoomImages.forEach(img => {
        img.style.transition = 'transform 0.3s ease';
        img.addEventListener('mouseenter', () => {
            img.style.transform = 'scale(1.05)';
        });
        img.addEventListener('mouseleave', () => {
            img.style.transform = 'scale(1)';
        });
    });

    // Form inputs focus animation
    const formInputs = document.querySelectorAll('.form-control, .form-select');
    formInputs.forEach(input => {
        input.addEventListener('focus', () => {
            input.parentElement.classList.add('input-focused');
        });
        input.addEventListener('blur', () => {
            input.parentElement.classList.remove('input-focused');
        });
    });

});

/**
 * Task Completion Confetti Animation
 * Triggered when a task is marked as completed
 */
function fireConfetti() {
    // We'll dynamically load canvas-confetti if it's called
    if (typeof confetti === 'undefined') {
        const script = document.createElement('script');
        script.src = 'https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js';
        script.onload = function() {
            triggerConfettiEffect();
        };
        document.head.appendChild(script);
    } else {
        triggerConfettiEffect();
    }
}

function triggerConfettiEffect() {
    var count = 200;
    var defaults = {
        origin: { y: 0.7 }
    };

    function fire(particleRatio, opts) {
        confetti(Object.assign({}, defaults, opts, {
            particleCount: Math.floor(count * particleRatio)
        }));
    }

    fire(0.25, { spread: 26, startVelocity: 55 });
    fire(0.2, { spread: 60 });
    fire(0.35, { spread: 100, decay: 0.91, scalar: 0.8 });
    fire(0.1, { spread: 120, startVelocity: 25, decay: 0.92, scalar: 1.2 });
    fire(0.1, { spread: 120, startVelocity: 45 });
}
