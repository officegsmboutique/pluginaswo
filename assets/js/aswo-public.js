/* global aswoPublic */
(function ($) {
    'use strict';

    /* =========================================================================
       Utilities
       ========================================================================= */

    /**
     * Show a toast notification.
     * @param {string} message
     * @param {string} type  'success'|'error'|'info'
     */
    function showToast(message, type) {
        type = type || 'info';
        var $toast = $('#aswo-toast');
        if (!$toast.length) { return; }
        var $item = $('<div/>', { 'class': 'aswo-toast-item ' + type, text: message });
        $toast.append($item);
        setTimeout(function () { $item.remove(); }, 4200);
    }

    /** Debounce helper */
    function debounce(fn, delay) {
        var timer;
        return function () {
            clearTimeout(timer);
            var args = arguments, ctx = this;
            timer = setTimeout(function () { fn.apply(ctx, args); }, delay);
        };
    }

    /** Format price string */
    function formatPrice(price) {
        return parseFloat(price).toFixed(2) + ' ' + aswoPublic.currency;
    }

    /** Update the mini-cart badge(s) */
    function updateCartBadge(count) {
        $('.aswo-cart-badge').text(count);
        if (count > 0) {
            $('.aswo-cart-badge').show();
        } else {
            $('.aswo-cart-badge').hide();
        }
    }

    /* =========================================================================
       Search Page
       ========================================================================= */

    var currentPage = 1;
    var currentQuery = '';

    /** Build an article card from a data object */
    function buildCard(article) {
        var $tpl = $('#aswo-card-template');
        if (!$tpl.length) { return $(); }
        var $card = $($tpl.html());

        var imgSrc = article.image_url || article.img || '';
        $card.find('.aswo-card-img img').attr({ src: imgSrc, alt: article.name || '' });
        $card.find('.aswo-article-no').text(article.article_no || article.artnr || '');
        $card.find('.aswo-article-name, h3').text(article.name || article.title || '');
        $card.find('.aswo-price').text(formatPrice(article.price || 0));

        var available = article.available || article.availability;
        var $avail = $card.find('.aswo-availability');
        if (available) {
            $avail.text(aswoPublic.i18n.inStock).addClass('in-stock');
        } else {
            $avail.text(aswoPublic.i18n.outOfStock).addClass('out-of-stock');
        }

        $card.find('.aswo-add-to-cart-btn').on('click', function () {
            var qty = parseInt($card.find('.aswo-qty-input').val(), 10) || 1;
            addToCart(article, qty);
        });

        $card.find('.aswo-detail-btn').on('click', function () {
            loadArticleDetail(article.article_no || article.artnr);
        });

        // store data on card for later
        $card.data('article', article);
        return $card;
    }

    /** Render search results */
    function renderResults(data) {
        var $grid = $('#aswo-results-grid');
        $grid.empty();

        var articles = data.articles || data.results || data.items || [];
        if (!articles.length) {
            $('#aswo-no-results').show();
            return;
        }

        $.each(articles, function (i, article) {
            $grid.append(buildCard(article));
        });

        renderPagination(data);
    }

    /** Render pagination */
    function renderPagination(data) {
        var $pag = $('#aswo-pagination');
        $pag.empty();

        var total = parseInt(data.total || 0, 10);
        var limit = parseInt(data.limit || aswoPublic.resultsPerPage || 20, 10);
        if (!total || !limit) { return; }

        var pages = Math.ceil(total / limit);
        if (pages <= 1) { return; }

        for (var p = 1; p <= pages; p++) {
            (function (page) {
                var $btn = $('<button/>', {
                    'class': 'aswo-page-btn' + (page === currentPage ? ' active' : ''),
                    text: page
                }).on('click', function () {
                    currentPage = page;
                    doSearch(currentQuery, page);
                });
                $pag.append($btn);
            }(p));
        }
    }

    /** Perform AJAX search */
    function doSearch(query, page) {
        if (!query) { return; }
        currentQuery = query;
        currentPage = page || 1;

        $('#aswo-loading').show();
        $('#aswo-no-results').hide();
        $('#aswo-results-grid').empty();
        $('#aswo-pagination').empty();
        $('#aswo-product-detail').hide();

        $.get(aswoPublic.ajaxUrl, {
            action: 'aswo_search',
            nonce:  aswoPublic.nonce,
            query:  query,
            page:   currentPage
        })
        .done(function (res) {
            if (res.success) {
                renderResults(res.data);
            } else {
                showToast((res.data && res.data.message) || aswoPublic.i18n.errorGeneric, 'error');
                $('#aswo-no-results').show();
            }
        })
        .fail(function () {
            showToast(aswoPublic.i18n.errorGeneric, 'error');
        })
        .always(function () {
            $('#aswo-loading').hide();
        });
    }

    /* ===== Suggestions / Autocomplete ===== */
    var $suggestBox = $('#aswo-suggestions');

    function fetchSuggestions(query) {
        if (!query || query.length < 2) {
            $suggestBox.empty().removeClass('open');
            return;
        }
        $.get(aswoPublic.ajaxUrl, {
            action: 'aswo_suggestions',
            nonce:  aswoPublic.nonce,
            query:  query
        }).done(function (res) {
            if (!res.success) { return; }
            var items = res.data.suggestions || res.data.items || res.data || [];
            $suggestBox.empty();
            if (!items.length) { $suggestBox.removeClass('open'); return; }
            $.each(items, function (i, item) {
                var label = typeof item === 'string' ? item : (item.label || item.name || '');
                $('<div/>', { 'class': 'aswo-suggestion-item', text: label })
                    .on('click', function () {
                        $('#aswo-search-input').val(label);
                        $suggestBox.empty().removeClass('open');
                        doSearch(label, 1);
                    })
                    .appendTo($suggestBox);
            });
            $suggestBox.addClass('open');
        });
    }

    var debouncedSuggest = debounce(fetchSuggestions, 300);

    /* ===== Search Init ===== */
    $(document).on('input', '#aswo-search-input', function () {
        debouncedSuggest($(this).val());
    });

    $(document).on('click', '#aswo-search-btn', function () {
        var q = $('#aswo-search-input').val().trim();
        $suggestBox.removeClass('open');
        doSearch(q, 1);
    });

    $(document).on('keydown', '#aswo-search-input', function (e) {
        if (e.key === 'Enter') {
            $suggestBox.removeClass('open');
            doSearch($(this).val().trim(), 1);
        }
    });

    // Close suggestions on outside click
    $(document).on('click', function (e) {
        if (!$(e.target).closest('.aswo-autocomplete-wrap').length) {
            $suggestBox.removeClass('open');
        }
    });

    /* ===== Tabs ===== */
    $(document).on('click', '.aswo-tab', function () {
        var tab = $(this).data('tab');
        $('.aswo-tab').removeClass('active');
        $(this).addClass('active');
        $('.aswo-tab-content').removeClass('active');
        $('#aswo-tab-' + tab).addClass('active');
    });

    /* ===== Appliance Search ===== */
    $(document).on('click', '#aswo-appliance-btn', function () {
        var q = $('#aswo-appliance-input').val().trim();
        if (!q) { return; }

        $('#aswo-loading').show();

        $.get(aswoPublic.ajaxUrl, {
            action: 'aswo_appliance_search',
            nonce:  aswoPublic.nonce,
            query:  q
        })
        .done(function (res) {
            var $list = $('#aswo-appliance-results').empty();
            if (!res.success) {
                showToast((res.data && res.data.message) || aswoPublic.i18n.errorGeneric, 'error');
                return;
            }
            var items = res.data.appliances || res.data.items || res.data || [];
            if (!items.length) {
                $list.html('<p>' + aswoPublic.i18n.noResults + '</p>');
                return;
            }
            $.each(items, function (i, appliance) {
                var name = appliance.name || appliance.label || JSON.stringify(appliance);
                var id   = appliance.id || appliance.appliance_id || '';
                $('<div/>', { 'class': 'aswo-appliance-item' })
                    .text(name)
                    .on('click', function () { loadApplianceArticles(id); })
                    .appendTo($list);
            });
        })
        .fail(function () {
            showToast(aswoPublic.i18n.errorGeneric, 'error');
        })
        .always(function () {
            $('#aswo-loading').hide();
        });
    });

    function loadApplianceArticles(applianceId) {
        $('#aswo-loading').show();
        $.get(aswoPublic.ajaxUrl, {
            action:       'aswo_appliance_search',
            nonce:        aswoPublic.nonce,
            appliance_id: applianceId
        })
        .done(function (res) {
            if (res.success) { renderResults(res.data); }
        })
        .always(function () { $('#aswo-loading').hide(); });
    }

    /* ===== Product Detail ===== */
    function loadArticleDetail(articleNo) {
        if (!articleNo) { return; }
        $('#aswo-loading').show();

        $.get(aswoPublic.ajaxUrl, {
            action:     'aswo_article_detail',
            nonce:      aswoPublic.nonce,
            article_no: articleNo
        })
        .done(function (res) {
            if (!res.success) {
                showToast((res.data && res.data.message) || aswoPublic.i18n.errorGeneric, 'error');
                return;
            }
            renderDetail(res.data);
        })
        .fail(function () {
            showToast(aswoPublic.i18n.errorGeneric, 'error');
        })
        .always(function () { $('#aswo-loading').hide(); });
    }

    function renderDetail(article) {
        var $detail = $('#aswo-product-detail');
        $detail.find('#aswo-detail-image').attr({ src: article.image_url || article.img || '', alt: article.name || '' });
        $detail.find('#aswo-detail-article-no').text(article.article_no || article.artnr || '');
        $detail.find('#aswo-detail-name').text(article.name || article.title || '');
        $detail.find('#aswo-detail-description').text(article.description || '');
        $detail.find('#aswo-detail-price').text(formatPrice(article.price || 0));

        var available = article.available || article.availability;
        var $avail = $detail.find('#aswo-detail-availability');
        $avail.removeClass('in-stock out-of-stock');
        if (available) {
            $avail.text(aswoPublic.i18n.inStock).addClass('in-stock');
        } else {
            $avail.text(aswoPublic.i18n.outOfStock).addClass('out-of-stock');
        }

        // Compatible appliances
        var $compat = $detail.find('#aswo-detail-compatibles').empty();
        var compat = article.appliances || article.compatible_appliances || [];
        if (compat.length) {
            var $ul = $('<ul/>', { 'class': 'aswo-compat-list' });
            $.each(compat, function (i, a) {
                $('<li/>').text(typeof a === 'string' ? a : (a.name || '')).appendTo($ul);
            });
            $compat.append('<h4>' + aswoPublic.i18n.compatTitle + '</h4>').append($ul);
        }

        $detail.data('article', article).show();
        $('html, body').animate({ scrollTop: $detail.offset().top - 80 }, 300);

        $detail.find('#aswo-detail-add-cart').off('click').on('click', function () {
            var qty = parseInt($detail.find('#aswo-detail-qty').val(), 10) || 1;
            addToCart(article, qty);
        });
    }

    // Back button
    $(document).on('click', '.aswo-back-btn', function () {
        $('#aswo-product-detail').hide();
        $('html, body').animate({ scrollTop: $('#aswo-results-area').offset().top - 80 }, 300);
    });

    /* =========================================================================
       Add to Cart
       ========================================================================= */

    function addToCart(article, qty) {
        $.post(aswoPublic.ajaxUrl, {
            action:     'aswo_add_to_cart',
            nonce:      aswoPublic.nonce,
            article_no: article.article_no || article.artnr || '',
            quantity:   qty || 1,
            name:       article.name || article.title || '',
            price:      article.price || 0,
            image_url:  article.image_url || article.img || ''
        })
        .done(function (res) {
            if (res.success) {
                showToast(aswoPublic.i18n.addedToCart, 'success');
                updateCartBadge(res.data.count);
            } else {
                showToast((res.data && res.data.message) || aswoPublic.i18n.errorGeneric, 'error');
            }
        })
        .fail(function () {
            showToast(aswoPublic.i18n.errorGeneric, 'error');
        });
    }

    /* =========================================================================
       Cart Page
       ========================================================================= */

    // Remove item
    $(document).on('click', '.aswo-remove-btn', function () {
        var articleNo = $(this).data('article');
        if (!confirm(aswoPublic.i18n.confirmRemove)) { return; }

        var $row = $(this).closest('.aswo-cart-row');
        $.post(aswoPublic.ajaxUrl, {
            action:     'aswo_remove_from_cart',
            nonce:      aswoPublic.nonce,
            article_no: articleNo
        })
        .done(function (res) {
            if (res.success) {
                $row.fadeOut(300, function () { $(this).remove(); refreshCartTotals(res.data); });
                showToast(aswoPublic.i18n.removedFromCart, 'info');
                updateCartBadge(res.data.count);
            } else {
                showToast((res.data && res.data.message) || aswoPublic.i18n.errorGeneric, 'error');
            }
        });
    });

    // Update cart
    $(document).on('click', '#aswo-update-cart', function () {
        var requests = [];
        $('.aswo-cart-qty').each(function () {
            var articleNo = $(this).data('article');
            var qty       = parseInt($(this).val(), 10) || 1;
            requests.push($.post(aswoPublic.ajaxUrl, {
                action:     'aswo_update_cart',
                nonce:      aswoPublic.nonce,
                article_no: articleNo,
                quantity:   qty
            }));
        });
        $.when.apply($, requests).done(function () {
            // Reload to reflect updated line totals
            location.reload();
        });
    });

    function refreshCartTotals(data) {
        $('#aswo-cart-total').find('strong').text(parseFloat(data.total).toFixed(2) + ' ' + aswoPublic.currency);
        updateCartBadge(data.count);
    }

    // Show order form
    $(document).on('click', '#aswo-checkout-btn', function () {
        $('#aswo-cart-form').hide();
        $(this).closest('.aswo-cart-actions').hide();
        $('#aswo-order-form-wrap').show();
        $('html, body').animate({ scrollTop: $('#aswo-order-form-wrap').offset().top - 80 }, 300);
    });

    // Back to cart from order form
    $(document).on('click', '#aswo-back-to-cart', function () {
        $('#aswo-order-form-wrap').hide();
        $('#aswo-cart-form').show();
        $('.aswo-cart-actions').show();
    });

    /* ===== Order Form Submit ===== */
    $(document).on('submit', '#aswo-order-form', function (e) {
        e.preventDefault();

        // Basic validation
        var valid = true;
        $(this).find('[required]').each(function () {
            if (!$(this).val().trim()) {
                $(this).addClass('aswo-invalid');
                valid = false;
            } else {
                $(this).removeClass('aswo-invalid');
            }
        });

        if (!valid) {
            showToast(aswoPublic.i18n.requiredFields, 'error');
            return;
        }

        var email = $('#aswo-email').val();
        if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            $('#aswo-email').addClass('aswo-invalid');
            showToast(aswoPublic.i18n.invalidEmail, 'error');
            return;
        }

        var $btn = $('#aswo-place-order-btn').prop('disabled', true).text(aswoPublic.i18n.processing);

        $.post(aswoPublic.ajaxUrl,
            $.extend({ action: 'aswo_place_order', nonce: aswoPublic.nonce },
                objectifyForm($(this).serializeArray()))
        )
        .done(function (res) {
            if (res.success) {
                $('#aswo-order-form-wrap').hide();
                var orderId = (res.data && res.data.order_id) ? aswoPublic.i18n.orderIdLabel + res.data.order_id : '';
                $('#aswo-order-id').text(orderId);
                $('#aswo-order-success').show();
                updateCartBadge(0);
            } else {
                showToast((res.data && res.data.message) || aswoPublic.i18n.errorGeneric, 'error');
            }
        })
        .fail(function () {
            showToast(aswoPublic.i18n.errorGeneric, 'error');
        })
        .always(function () {
            $btn.prop('disabled', false).text(aswoPublic.i18n.placeOrder);
        });
    });

    /** Convert jQuery serializeArray to plain object */
    function objectifyForm(arr) {
        var obj = {};
        $.each(arr, function (i, item) { obj[item.name] = item.value; });
        return obj;
    }

    /* =========================================================================
       Init: update cart badge on page load
       ========================================================================= */
    if (typeof aswoPublic !== 'undefined' && aswoPublic.cartCount) {
        updateCartBadge(aswoPublic.cartCount);
    }

}(jQuery));
