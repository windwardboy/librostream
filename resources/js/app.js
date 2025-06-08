import './bootstrap';

// --- Continue Listening Feature (Audiobook Detail Page) ---

document.addEventListener('DOMContentLoaded', () => {
    // Use data-audiobook-slug instead of data-audiobook-id
    const audiobookContainer = document.querySelector('[data-audiobook-slug]');
    const mainAudioPlayer = document.getElementById('main-audio-player');

    if (audiobookContainer && mainAudioPlayer) {
        // Use slug instead of ID
        const audiobookSlug = audiobookContainer.dataset.audiobookSlug;
        const sectionElements = audiobookContainer.querySelectorAll('li[data-section-id]');
        // Update localStorage key to use slug
        const localStorageKey = `audiobook_progress_${audiobookSlug}`;

        // Store section data in an array for easy access
        const sectionsData = Array.from(sectionElements).map(li => ({
            id: li.dataset.sectionId,
            src: li.dataset.src,
            title: li.dataset.title,
            element: li // Keep a reference to the list item element
        }));

        let currentSectionIndex = -1; // Index of the currently playing section in sectionsData

        // Function to update UI highlight for the active section
        const updateActiveSectionHighlight = (activeSectionId) => {
            sectionsData.forEach(section => {
                // Remove highlight classes and active text color from all sections
                section.element.classList.remove('bg-blue-600', 'dark:bg-blue-800', 'text-white');
                // Re-add default background and text colors to inactive sections
                section.element.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-white'); // Assuming these are the default classes

                if (section.id === activeSectionId) {
                    // Remove default background and text colors from the active section
                    section.element.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'text-gray-800', 'dark:text-white');
                    // Add highlight classes and active text color
                    section.element.classList.add('bg-blue-600', 'dark:bg-blue-800', 'text-white');
                }
            });
        };

        // Load saved progress on page load
        const savedProgress = localStorage.getItem(localStorageKey);
        if (savedProgress) {
            try {
                const { sectionId, time } = JSON.parse(savedProgress);
                const savedSection = sectionsData.find(section => section.id == sectionId); // Use == for potential type coercion

                if (savedSection && savedSection.src) {
                    // Load the saved section into the main player
                    mainAudioPlayer.src = savedSection.src;
                    // Set the playback position
                    mainAudioPlayer.currentTime = time;
                    currentSectionIndex = sectionsData.indexOf(savedSection); // Update current index
                    updateActiveSectionHighlight(savedSection.id); // Highlight the saved section
                    console.log(`Resumed playback for section ${savedSection.title} (ID: ${sectionId}) at ${time} seconds.`);
                    // Optional: Add visual indication or prompt to resume
                } else if (savedSection && !savedSection.src) {
                     console.warn(`Saved section ${savedSection.title} (ID: ${sectionId}) has no source URL.`);
                     localStorage.removeItem(localStorageKey); // Clear progress if source is missing
                } else {
                     console.warn(`Saved section ID ${sectionId} not found on this page.`);
                     localStorage.removeItem(localStorageKey); // Clear invalid data
                }
            } catch (e) {
                console.error("Failed to parse saved progress from localStorage:", e);
                localStorage.removeItem(localStorageKey); // Clear invalid data
            }
        } else if (sectionsData.length > 0 && sectionsData[0].src) {
             // If no saved progress, load the first section by default (but don't play)
             mainAudioPlayer.src = sectionsData[0].src;
             currentSectionIndex = 0;
             updateActiveSectionHighlight(sectionsData[0].id);
        }


        // Add click listeners to section list items
        sectionsData.forEach((section, index) => {
            if (section.element && section.src) { // Only add listener if element and source exist
                section.element.addEventListener('click', () => {
                    // Load this section into the main player
                    mainAudioPlayer.src = section.src;
                    mainAudioPlayer.currentTime = 0; // Start from the beginning when clicked
                    mainAudioPlayer.play(); // Start playback
                    currentSectionIndex = index; // Update current index
                    updateActiveSectionHighlight(section.id); // Highlight the clicked section
                    console.log(`Playing section ${section.title} (ID: ${section.id}).`);
                });
            } else if (section.element && !section.src) {
                 // Add a class or style to indicate no source
                 section.element.classList.add('opacity-50', 'cursor-not-allowed');
                 section.element.title = 'Audio source not available for this section.';
            }
        });


        // Add event listeners to the main audio player
        mainAudioPlayer.addEventListener('timeupdate', () => {
            // Save progress periodically while playing
            if (!mainAudioPlayer.paused && mainAudioPlayer.currentTime > 0) {
                 // Save roughly every 5 seconds or if near the end
                if (mainAudioPlayer.currentTime % 5 < 0.1 || (mainAudioPlayer.duration > 0 && mainAudioPlayer.currentTime > mainAudioPlayer.duration - 1)) {
                     const currentSection = sectionsData[currentSectionIndex];
                     if (currentSection) {
                         const progress = {
                            sectionId: currentSection.id,
                            time: mainAudioPlayer.currentTime,
                            timestamp: Date.now() // Add timestamp
                        };
                        localStorage.setItem(localStorageKey, JSON.stringify(progress));
                        // console.log(`Saved progress for section ${currentSection.title} at ${mainAudioPlayer.currentTime} seconds.`);
                     }
                }
            }
        });

        mainAudioPlayer.addEventListener('pause', () => {
            const currentSection = sectionsData[currentSectionIndex];
            if (currentSection) {
                const progress = {
                    sectionId: currentSection.id,
                    time: mainAudioPlayer.currentTime,
                    timestamp: Date.now() // Add timestamp
                };
                localStorage.setItem(localStorageKey, JSON.stringify(progress));
                console.log(`Saved progress for section ${currentSection.title} at ${mainAudioPlayer.currentTime} seconds.`);
            }
        });

        mainAudioPlayer.addEventListener('ended', () => {
            console.log(`Audio ended for section index ${currentSectionIndex}.`);
            const nextSectionIndex = currentSectionIndex + 1;

            if (nextSectionIndex < sectionsData.length) {
                const nextSection = sectionsData[nextSectionIndex];
                if (nextSection.src) {
                    // Load and play the next section
                    mainAudioPlayer.src = nextSection.src;
                    mainAudioPlayer.currentTime = 0;
                    mainAudioPlayer.play();
                    currentSectionIndex = nextSectionIndex; // Update current index
                    updateActiveSectionHighlight(nextSection.id); // Highlight the next section
                    console.log(`Playing next section: ${nextSection.title} (ID: ${nextSection.id}).`);

                    // Save progress for the start of the next section
                     const progress = {
                        sectionId: nextSection.id,
                        time: 0,
                        timestamp: Date.now() // Add timestamp
                    };
                    localStorage.setItem(localStorageKey, JSON.stringify(progress));

                } else {
                     console.warn(`Next section ${nextSection.title} (ID: ${nextSection.id}) has no source URL. Stopping playback.`);
                     // If next section has no source, stop sequential playback
                     currentSectionIndex = -1; // Reset index
                     localStorage.removeItem(localStorageKey); // Clear progress
                     updateActiveSectionHighlight(null); // Remove highlight
                }
            } else {
                // No more sections, audiobook finished
                console.log("Audiobook finished.");
                currentSectionIndex = -1; // Reset index
                localStorage.removeItem(localStorageKey); // Clear progress for finished book
                updateActiveSectionHighlight(null); // Remove highlight
            }
        });

        // Optional: Handle play event to ensure only this player is active (if needed)
        // mainAudioPlayer.addEventListener('play', () => {
        //     // Logic to pause other potential audio players on the page if any
        // });
    }
});

// --- End Continue Listening Feature (Audiobook Detail Page) ---

// --- Continue Listening Feature (Homepage - Display Recent) ---

document.addEventListener('DOMContentLoaded', () => {
    const continueListeningSection = document.getElementById('continue-listening-section');
    const continueListeningList = document.getElementById('continue-listening-list');

    if (continueListeningSection && continueListeningList) {
        const recentlyListened = [];

        // Collect all audiobook progress from localStorage
        for (let i = 0; i < localStorage.length; i++) {
            const key = localStorage.key(i);
            if (key.startsWith('audiobook_progress_')) {
                const audiobookSlug = key.replace('audiobook_progress_', '');
                try {
                    const progress = JSON.parse(localStorage.getItem(key));
                    // Ensure progress data is valid and includes timestamp
                    if (progress && progress.sectionId !== undefined && progress.time !== undefined && progress.timestamp !== undefined) {
                        recentlyListened.push({
                            slug: audiobookSlug,
                            sectionId: progress.sectionId,
                            time: progress.time,
                            timestamp: progress.timestamp
                        });
                    } else {
                        console.warn(`Invalid progress data for key: ${key}`, progress);
                        // Optionally remove invalid data
                        // localStorage.removeItem(key);
                    }
                } catch (e) {
                    console.error(`Failed to parse localStorage key: ${key}`, e);
                    // Optionally remove corrupted data
                    // localStorage.removeItem(key);
                }
            }
        }

        // Sort by timestamp (most recent first)
        recentlyListened.sort((a, b) => b.timestamp - a.timestamp);

        // Limit to a reasonable number of recent items, e.g., 5
        const limitedRecentlyListened = recentlyListened.slice(0, 5);
        const limitedSlugsToFetch = limitedRecentlyListened.map(item => item.slug);


        if (limitedSlugsToFetch.length > 0) {
            // Fetch detailed audiobook data from the backend
            fetch('/api/audiobooks-by-slugs', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') // Include CSRF token for Laravel
                },
                body: JSON.stringify({ slugs: limitedSlugsToFetch })
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(audiobooksData => {
                // Map fetched data to recently listened items
                const itemsToDisplay = limitedRecentlyListened.map(progressItem => {
                    const audiobook = audiobooksData.find(book => book.slug === progressItem.slug);
                    if (audiobook) {
                        const currentSection = audiobook.sections.find(section => section.id == progressItem.sectionId); // Use == for potential type coercion
                        const sectionTitle = currentSection ? currentSection.title : 'Unknown Section';
                        const totalSections = audiobook.sections.length;
                        const sectionNumber = currentSection ? currentSection.section_number : 'N/A';

                        return `
                            <div class="bg-white dark:bg-gray-800 p-4 rounded-lg shadow-sm flex items-center space-x-4">
                                <a href="/audiobooks/${audiobook.slug}" class="flex-shrink-0">
                                    ${audiobook.cover_image ?
                                        `<img src="${audiobook.cover_image}" alt="Cover image for ${audiobook.title}" class="w-16 h-16 object-cover rounded-md">` :
                                        `<div class="w-16 h-16 bg-gray-300 dark:bg-gray-700 flex items-center justify-center rounded-md">
                                            <span class="text-gray-500 dark:text-gray-400 text-xs text-center">No Image</span>
                                        </div>`
                                    }
                                </a>
                                <div class="flex-grow">
                                    <a href="/audiobooks/${audiobook.slug}" class="text-gray-800 dark:text-white hover:underline font-medium text-lg truncate block">
                                        ${audiobook.title}
                                    </a>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 truncate">By: ${audiobook.author}</p>
                                    ${audiobook.narrator ? `<p class="text-sm text-gray-600 dark:text-gray-400 truncate">Narrated by: ${audiobook.narrator}</p>` : ''}
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                        Section ${sectionNumber}: ${sectionTitle} (${formatTime(progressItem.time)})
                                    </p>
                                </div>
                            </div>
                        `;
                    }
                    return ''; // Don't display if audiobook data wasn't fetched
                }).join('');

                if (itemsToDisplay) {
                    continueListeningList.innerHTML = itemsToDisplay;
                    continueListeningSection.classList.remove('hidden');
                } else {
                    continueListeningSection.classList.add('hidden');
                }
            })
            .catch(error => {
                console.error("Error fetching audiobook details for continue listening:", error);
                continueListeningSection.classList.add('hidden'); // Hide section on error
            });

        } else {
            // Hide the section if no items in local storage
             continueListeningSection.classList.add('hidden');
        }
    }
});

// Helper function to format time (optional)
function formatTime(seconds) {
    const h = Math.floor(seconds / 3600);
    const m = Math.floor((seconds % 3600) / 60);
    const s = Math.floor(seconds % 60);

    const parts = [];
    if (h > 0) parts.push(`${h}h`);
    if (m > 0 || h > 0) parts.push(`${m}m`); // Show minutes if > 0 or if hours are shown
    parts.push(`${s}s`); // Always show seconds

    return parts.join(' ');
}


// --- End Continue Listening Feature (Homepage - Display Recent) ---

// --- Responsive Header Menu ---

document.addEventListener('DOMContentLoaded', () => {
    const mobileMenuButton = document.getElementById('mobile-menu-button');
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenuButton && mobileMenu) {
        mobileMenuButton.addEventListener('click', () => {
            const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
            mobileMenu.classList.toggle('hidden');
            mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
        });
    }
});

// --- End Responsive Header Menu ---

// --- Dark Mode Toggle ---

document.addEventListener('DOMContentLoaded', () => {
    const themeToggleBtn = document.getElementById('theme-toggle');
    const lightIcon = document.getElementById('theme-toggle-light-icon');
    const darkIcon = document.getElementById('theme-toggle-dark-icon');
    const htmlElement = document.documentElement;

    // Check local storage for theme preference
    const currentTheme = localStorage.getItem('color-theme');

    if (currentTheme === 'dark' || (!currentTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
        htmlElement.classList.add('dark');
        darkIcon.classList.remove('hidden');
    } else {
        lightIcon.classList.remove('hidden');
    }

    themeToggleBtn.addEventListener('click', () => {
        // Toggle the 'dark' class on the html element
        htmlElement.classList.toggle('dark');

        // Update icons based on the current theme
        if (htmlElement.classList.contains('dark')) {
            lightIcon.classList.add('hidden');
            darkIcon.classList.remove('hidden');
            localStorage.setItem('color-theme', 'dark'); // Save preference
        } else {
            lightIcon.classList.remove('hidden');
            darkIcon.classList.add('hidden');
            localStorage.setItem('color-theme', 'light'); // Save preference
        }
    });
});

// --- End Dark Mode Toggle ---

// --- Service Worker Registration ---
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/service-worker.js')
            .then(registration => {
                console.log('Service Worker registered:', registration);
            })
            .catch(error => {
                console.error('Service Worker registration failed:', error);
            });
    });
}
// --- End Service Worker Registration ---

// --- Animated Counter for Features Widget ---
document.addEventListener('DOMContentLoaded', () => {
    const counters = document.querySelectorAll('.feature-count');
    const speed = 200; // The lower the number, the faster the count

    const observer = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const counter = entry.target;
                const updateCount = () => {
                    const target = +counter.getAttribute('data-count');
                    const count = +counter.innerText;

                    const inc = target / speed;

                    if (count < target) {
                        counter.innerText = Math.ceil(count + inc);
                        setTimeout(updateCount, 1);
                    } else {
                        counter.innerText = target.toLocaleString(); // Add commas to final number
                    }
                };
                updateCount();
                observer.unobserve(counter); // Stop observing after animation
            }
        });
    }, {
        threshold: 0.5 // Trigger when 50% of the element is visible
    });

    counters.forEach(counter => {
        observer.observe(counter);
    });
});
// --- End Animated Counter ---
