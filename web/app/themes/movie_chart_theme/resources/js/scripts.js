document.addEventListener('DOMContentLoaded', (event) => {

    // Open correct info window on list item click event
    const listItems = document.querySelectorAll('.movie-list-item');
    if (listItems) {
        const infoWindows = document.querySelectorAll('.movie-info');

        listItems.forEach((element) => {

            element.addEventListener('click', (e) => {
                const id = e.currentTarget.getAttribute('data-id');

                infoWindows.forEach((infoWindow) => {

                    let windowId = infoWindow.getAttribute('data-id');
                    if (windowId === id) {
                        infoWindow.classList.remove('hidden');

                        // Show container (for mobile)
                        const windowsContainer = document.querySelector('.movie-info-container');
                        if (windowsContainer) {
                            windowsContainer.classList.add('active');
                        }

                        // If mobile set the window scroll position as top of the infowindow so it look next to the i
                        let vpWidth = window.innerWidth || document.documentElement.clientWidth;

                        if (vpWidth < 768) {
                            infoWindow.style.top = window.scrollY + 'px';
                        } else {
                            infoWindow.style.top = 0 + 'px';
                        }

                        // Move close button
                        let closeBtn = document.querySelector('.close-info-container');
                        if (vpWidth < 768) {
                            closeBtn.style.top = (window.scrollY + 8) + 'px';
                        } else {
                            closeBtn.style.top = 8 + 'px';
                        }

                        // Hide list (for mobile)
                        const listContainer = document.querySelector('.movie-list-container');
                        if (listContainer) {
                            listContainer.classList.add('hidden');
                        }

                        // Hide interface
                        const genreMenuToggle = document.querySelector('.toggle-genre-button');
                        if (genreMenuToggle) {
                            genreMenuToggle.classList.add('hidden');
                        }

                        const liveSearchInput = document.querySelector('.live-search-input');
                        if (liveSearchInput) {
                            liveSearchInput.classList.add('hidden');
                        }
                    } else {
                        infoWindow.classList.add('hidden');
                    }
                });
            });
        });
    }

    // Close the info overlay (for mobile)
    const closeBtn = document.querySelector('.close-info-container');
    if (closeBtn) {
        closeBtn.addEventListener('click', (e) => {

            // Hide container
            const windowsContainer = document.querySelector('.movie-info-container');
            if (windowsContainer) {
                windowsContainer.classList.remove('active');
            }

            // Show list
            const listContainer = document.querySelector('.movie-list-container');
            if (listContainer) {
                listContainer.classList.remove('hidden');
            }

            // Show interface
            const genreMenuToggle = document.querySelector('.toggle-genre-button');
            if (genreMenuToggle) {
                genreMenuToggle.classList.remove('hidden');
            }

            const liveSearchInput = document.querySelector('.live-search-input');
            if (liveSearchInput) {
                liveSearchInput.classList.remove('hidden');
            }
        });
    }

    // Toggle genre menu
    const genreMenuToggle = document.querySelector('#toggle-genre-menu');
    if (genreMenuToggle) {
        genreMenuToggle.addEventListener('click', (e) => {

            const genreMenu = document.querySelector('.genre-menu');
            genreMenu.classList.toggle('active');
        });
    }
});


function liveSearch(input) {

    // Check filterdata is present
    if (typeof filterdata !== 'undefined') {
        let value = input.value;

        if (value.length > 2) {
            // filter items
            for (const key in filterdata) {
                if (filterdata.hasOwnProperty(key)) {
                    // Search the title for the input value
                    if (filterdata[key].toLowerCase().search(value.toLowerCase()) === -1) {
                        // Hide item if not found
                        let elements = document.querySelectorAll('[data-id="' + key + '"]');
                        if (elements) {
                            elements.forEach((element) => {
                                element.classList.add('filtered');
                            });
                        }
                    } else {
                        // Show item if found
                        let elements = document.querySelectorAll('[data-id="' + key + '"]');

                        if (elements) {
                            elements.forEach((element) => {
                                element.classList.remove('filtered');
                            });
                        }
                    }
                }
            }
        } else {
            //reset filter
            const items = document.querySelectorAll('.movie-list-item, .movie-info');

            items.forEach((el) => {

                el.classList.remove('filtered');
            });
        }
    }

}