/*function + dropdown*/

document.addEventListener('DOMContentLoaded', function () {
    // Get all collapsible elements
    const collapsibles = document.querySelectorAll('.collapsible');

    // Add click event listener to each collapsible section
    collapsibles.forEach(function (collapsible) {
        collapsible.addEventListener('click', function () {
            const content = collapsible.nextElementSibling;

            // Toggle the active class for styling
            collapsible.classList.toggle('active');

            // Toggle the display of the content
            if (content.style.display === 'block') {
                content.style.display = 'none';
            } else {
                content.style.display = 'block';
            }
        });
    });
});

/*function for calendar*/

