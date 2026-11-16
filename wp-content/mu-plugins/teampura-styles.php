<?php
/**
 * Plugin Name: Teampura Industry Styles Loader
 * Description: Loads Google Fonts, FontAwesome 6, teampura-industry.css, personal member SSR nav filter, and smooth scroll animations.
 */

if (!defined('ABSPATH')) {
    exit;
}

function teampura_get_member_map() {
    return [
        'justin-maurin'           => 'Justin Maurin',
        'mike-iris-ebojo'         => 'Mike Iris Ebojo',
        'christian-angelo-enerio' => 'Christian Angelo Enerio',
        'rhea-mae-marandang'      => 'Rhea Mae Marandang',
    ];
}

function teampura_current_member_info() {
    if (!is_page()) {
        return ['', ''];
    }
    global $post;
    $map = teampura_get_member_map();
    if ($post && isset($map[$post->post_name])) {
        return [$post->post_name, $map[$post->post_name]];
    }
    return ['', ''];
}

function teampura_enqueue_industry_assets() {
    wp_enqueue_style(
        'teampura-google-fonts',
        'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Poppins:wght@500;600;700;800&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'teampura-fontawesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css',
        [],
        '6.5.1'
    );

    $css_path = get_template_directory() . '/assets/css/teampura-industry.css';
    $css_url  = get_template_directory_uri() . '/assets/css/teampura-industry.css';
    if (file_exists($css_path)) {
        wp_enqueue_style(
            'teampura-industry-css',
            $css_url,
            [],
            filemtime($css_path)
        );
    }
}

add_action('wp_enqueue_scripts', 'teampura_enqueue_industry_assets', 999);
add_action('elementor/preview/enqueue_styles', 'teampura_enqueue_industry_assets', 999);

// Ensure mobile_menu uses the same menu as primary so Sample Page never appears in mobile fallback
add_filter('theme_mod_nav_menu_locations', function ($locations) {
    if (is_array($locations) && !empty($locations['primary']) && empty($locations['mobile_menu'])) {
        $locations['mobile_menu'] = $locations['primary'];
    }
    return $locations;
});

// Server-side personal navbar transformation on individual member pages
add_filter('wp_nav_menu_objects', function ($items) {
    list($slug, $member_name) = teampura_current_member_info();
    if (!$member_name || empty($items)) {
        return $items;
    }

    $top_items = [];
    $sub_items = [];
    $dropdown_parent_id = 0;

    foreach ($items as $item) {
        if ((int) $item->menu_item_parent === 0) {
            $top_items[] = $item;
            if (in_array('menu-item-has-children', (array) $item->classes, true) || stripos($item->title, 'Team') !== false) {
                $dropdown_parent_id = (int) $item->ID;
            }
        } else {
            $sub_items[] = $item;
        }
    }

    if (count($top_items) < 5) {
        return $items;
    }

    $make_item = function ($id, $title, $url, $parent = 0, $classes = []) {
        $obj = new stdClass();
        $obj->ID = $id;
        $obj->db_id = $id;
        $obj->title = $title;
        $obj->url = $url;
        $obj->menu_item_parent = $parent;
        $obj->object_id = $id;
        $obj->object = 'custom';
        $obj->type = 'custom';
        $obj->type_label = 'Custom Link';
        $obj->classes = array_merge(['menu-item', 'menu-item-type-custom', 'menu-item-object-custom'], $classes);
        $obj->target = '';
        $obj->attr_title = '';
        $obj->description = '';
        $obj->xfn = '';
        $obj->status = 'publish';
        $obj->current = false;
        $obj->current_item_ancestor = false;
        $obj->current_item_parent = false;
        return $obj;
    };

    $home_item     = clone $top_items[0];
    $home_item->title = 'Home';
    $home_item->url   = '#member-hero-section';

    $about_item    = clone $top_items[1];
    $about_item->title = 'About';
    $about_item->url   = '#member-about';

    $switch_item   = clone $top_items[2];
    $switch_item->title = 'Switch Member';
    $switch_item->url   = '#member-switcher-section';

    $projects_item = clone $top_items[3];
    $projects_item->title = 'Projects';
    $projects_item->url   = '#member-projects';

    $contact_item  = clone $top_items[count($top_items) - 1];
    $contact_item->title = 'Contact Me';
    $contact_item->url   = '#member-contact';

    $services_item = $make_item(9901, 'Services', '#member-services');
    $skills_item   = $make_item(9902, 'Skills', '#member-skills');

    $result = [
        $home_item,
        $services_item,
        $about_item,
        $skills_item,
        $projects_item,
        $switch_item,
    ];

    if ($dropdown_parent_id) {
        $result[] = $make_item(9903, '← Team Teampura Home', home_url('/'), $dropdown_parent_id);
        foreach ($sub_items as $sub) {
            $result[] = $sub;
        }
    }

    $result[] = $contact_item;
    return $result;
}, 20);

add_action('wp_footer', function () {
    list($current_slug, $current_member_name) = teampura_current_member_info();
    ?>
    <script id="teampura-dynamic-nav-and-motion">
    (function () {
        const homeUrl = <?php echo wp_json_encode(home_url('/')); ?>;
        const memberName = <?php echo wp_json_encode($current_member_name); ?>;
        const memberSlug = <?php echo wp_json_encode($current_slug); ?>;

        // 1. Update Brand Logo on Personal Member Pages
        if (memberName) {
            document.querySelectorAll('#masthead .site-title a').forEach(function (a) {
                a.textContent = memberName + '.';
                a.classList.add('tp-member-initial');
                a.setAttribute('data-initial', memberName.charAt(0).toUpperCase());
                a.setAttribute('href', '#member-hero-section');
                if (!a.parentElement.querySelector('.tp-member-brand-badge')) {
                    const back = document.createElement('a');
                    back.className = 'tp-member-brand-badge';
                    back.href = homeUrl;
                    back.textContent = '← Team Home';
                    a.parentElement.appendChild(back);
                }
            });
        }

        // 2. Sticky Pill Header Scroll Shrinking Animation
        function handleHeaderScroll() {
            const masthead = document.getElementById('masthead');
            if (!masthead) return;
            if (window.scrollY > 25) {
                masthead.classList.add('tp-header-scrolled');
            } else {
                masthead.classList.remove('tp-header-scrolled');
            }
        }
        window.addEventListener('scroll', handleHeaderScroll, { passive: true });
        handleHeaderScroll();

        // 3. Clean Scroll-Spy for Active Nav Link
        function updateActiveNav() {
            const navLinks = document.querySelectorAll('#masthead .main-header-menu > li.menu-item > a.menu-link');
            const sections = [];
            navLinks.forEach(function (link) {
                link.parentElement.classList.remove('tp-active-nav');
                const href = link.getAttribute('href') || '';
                const hashIdx = href.indexOf('#');
                if (hashIdx !== -1) {
                    const id = href.slice(hashIdx + 1);
                    const el = document.getElementById(id);
                    if (el) sections.push({ el: el, li: link.parentElement });
                }
            });

            const scrollPos = window.scrollY + 140;
            let matched = null;
            sections.forEach(function (item) {
                if (item.el.offsetTop <= scrollPos) {
                    matched = item.li;
                }
            });

            if (matched) {
                matched.classList.add('tp-active-nav');
            } else if (navLinks.length > 0) {
                navLinks[0].parentElement.classList.add('tp-active-nav');
            }

            if (memberSlug) {
                document.querySelectorAll('#masthead .sub-menu a.menu-link').forEach(function (sa) {
                    if ((sa.getAttribute('href') || '').indexOf(memberSlug) !== -1) {
                        sa.parentElement.classList.add('tp-active-nav');
                    }
                });
            }
        }

        window.addEventListener('scroll', updateActiveNav, { passive: true });
        document.addEventListener('DOMContentLoaded', updateActiveNav);
        updateActiveNav();

        // 4. Typewriter Effect for Profession & Animated Intro
        function initTypewriter() {
            const headline = document.querySelector('.tp-hero-headline');
            if (!headline) return;

            const professionMap = {
                'justin-maurin': [
                    'Lead Full-Stack Programmer',
                    'Laravel & React Engineer',
                    'DTS Fleet Platform Architect',
                    'BSIT Capstone Developer'
                ],
                'mike-iris-ebojo': [
                    'Agile Project Manager',
                    'Scrum Master & Coordinator',
                    'Dennis Trucking Ops Lead',
                    'BSIT Capstone Lead'
                ],
                'christian-angelo-enerio': [
                    'Lead System Analyst',
                    'ERD & Database Designer',
                    'UI/UX & Workflow Architect',
                    'BSIT Process Engineer'
                ],
                'rhea-mae-marandang': [
                    'Lead Technical Writer',
                    'System Documentation Specialist',
                    'SRS & User Manual Author',
                    'BSIT Quality Assurance Lead'
                ]
            };

            const defaultRoles = [
                'Full-Stack BSIT Innovators',
                'Dennis Trucking Platform Creators',
                'Laravel & React Engineers',
                'Agile Capstone Team'
            ];

            const roles = (memberSlug && professionMap[memberSlug]) ? professionMap[memberSlug] : defaultRoles;
            const prefix = memberName ? ('I’m <span class="tp-highlight-name">' + memberName + ',</span>') : 'We’re <span class="tp-highlight-name">Team Teampura,</span>';
            const suffix = '<span class="tp-hero-origin-line" style="display:block; font-size:clamp(1.4rem, 2.6vw, 2.05rem); color:var(--tp-slate); font-weight:700; margin-top:2px;">From Opol Community College.</span>';

            headline.innerHTML = prefix + '<span class="tp-typewriter-container"><span class="tp-typewriter-text"></span><span class="tp-typewriter-cursor">|</span></span>' + suffix;

            const textEl = headline.querySelector('.tp-typewriter-text');
            if (!textEl) return;

            let roleIdx = 0;
            let charIdx = 0;
            let isDeleting = false;

            function typeTick() {
                const currentRole = roles[roleIdx];
                if (isDeleting) {
                    charIdx--;
                    textEl.textContent = currentRole.substring(0, charIdx);
                } else {
                    charIdx++;
                    textEl.textContent = currentRole.substring(0, charIdx);
                }

                let speed = isDeleting ? 38 : 70;

                if (!isDeleting && charIdx === currentRole.length) {
                    speed = 1900;
                    isDeleting = true;
                } else if (isDeleting && charIdx === 0) {
                    isDeleting = false;
                    roleIdx = (roleIdx + 1) % roles.length;
                    speed = 450;
                }

                setTimeout(typeTick, speed);
            }

            typeTick();
        }

        // 5. Scroll-Reveal & Animated Counters
        function initAnimations() {
            // A. Section-level smooth entrance transitions
            const sectionSelectors = [
                '#hero-section', '#member-hero-section',
                '#services-section', '#member-services',
                '#about-section', '#member-about',
                '#stats-section', '#team-section',
                '#projects-section', '#member-projects',
                '#member-deliverables', '#member-education',
                '#member-hobbies', '#member-skills',
                '#member-certifications', '#member-cv-section',
                '#faq-section', '#contact-section',
                '#member-contact', '#member-switcher-section',
                'footer'
            ];

            const sections = document.querySelectorAll(sectionSelectors.join(', '));
            const cardTargets = document.querySelectorAll(
                '#services-grid-row > .e-con-inner > .e-con, #team-grid-row > .e-con-inner > .e-con, #projects-grid-row > .e-con-inner > .e-con, #about-split-row > .e-con-inner > .e-con, #stats-grid-row > .e-con-inner > .e-con, #member-services-grid > .e-con-inner > .e-con, #member-projects-grid > .e-con-inner > .e-con, #member-deliverables-grid > .e-con-inner > .e-con, #member-certs-grid > .e-con-inner > .e-con, #member-cv-grid > .e-con-inner > .e-con, #member-details-row > .e-con-inner > .e-con, #member-education-grid > .e-con-inner > .e-con, #member-hobbies-grid > .e-con-inner > .e-con, #member-switcher-row > .e-con-inner > .e-con'
            );

            if ('IntersectionObserver' in window) {
                // Section Observer
                const sectionObs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('tp-section-revealed');
                            sectionObs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.04 });

                sections.forEach(function (sec) {
                    sec.classList.add('tp-section-reveal');
                    sectionObs.observe(sec);
                });

                // Card Stagger Observer
                const cardObs = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('tp-revealed');
                            cardObs.unobserve(entry.target);
                        }
                    });
                }, { threshold: 0.08 });

                cardTargets.forEach(function (el, idx) {
                    el.classList.add('tp-reveal');
                    el.style.transitionDelay = ((idx % 4) * 85) + 'ms';
                    cardObs.observe(el);
                });
            } else {
                sections.forEach(function (sec) { sec.classList.add('tp-section-revealed'); });
                cardTargets.forEach(function (el) { el.classList.add('tp-revealed'); });
            }

            // Animated Counters
            document.querySelectorAll('.tp-count-num[data-target]').forEach(function (counter) {
                const target = parseInt(counter.getAttribute('data-target'), 10);
                const suffix = counter.getAttribute('data-suffix') || '';
                if (!target) return;
                let current = 0;
                const step = Math.max(1, Math.ceil(target / 30));
                const timer = setInterval(function () {
                    current += step;
                    if (current >= target) {
                        counter.textContent = target + suffix;
                        clearInterval(timer);
                    } else {
                        counter.textContent = current + suffix;
                    }
                }, 28);
            });

            // Initialize Typewriter
            initTypewriter();
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initAnimations);
        } else {
            initAnimations();
        }
    })();
    </script>
    <?php
}, 99);
