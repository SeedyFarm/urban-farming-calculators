jQuery(function ($) {
	'use strict';

	/**
	 * Handles the logic for the Urban Farming Calculators.
	 */
	var ufcCalculators = {
		debounceTimer: null,

		/**
		 * Initialize the calculator event handlers.
		 */
		init: function () {
			$(document).on(
				'input',
				'.ufc-plant-search',
				this.handleSearchInput.bind(this)
			);
			$(document).on(
				'click',
				'.ufc-result-item',
				this.handleResultClick.bind(this)
			);
			$(document).on(
				'submit',
				'.ufc-calculator-form',
				this.handleFormSubmit.bind(this)
			);

			// Also handle unit switching for mulch calculator.
			$(document).on(
				'change',
				'#ufc-mulch-calculator .ufc-unit-radio',
				this.handleMulchUnitChange.bind(this)
			);
		},

		/**
		 * Handle the real-time search as a user types.
		 * @param {Event} e The input event.
		 */
		handleSearchInput: function (e) {
			var searchField = $(e.currentTarget);
			var form = searchField.closest('form');
			var searchTerm = searchField.val();
			var resultsContainer = form.find('.ufc-search-results');
			var hiddenInput = form.find('.ufc-plant-type-key');

			hiddenInput.val('');
			clearTimeout(this.debounceTimer);

			if (searchTerm.length < 2) {
				resultsContainer.empty().hide();
				return;
			}

			this.debounceTimer = setTimeout(
				function () {
					this.performAjaxSearch(searchTerm, resultsContainer);
				}.bind(this),
				300
			);
		},

		/**
		 * Perform the AJAX request to search for seeds.
		 * @param {string} searchTerm The term to search for.
		 * @param {jQuery} resultsContainer The jQuery object for the results container.
		 */
		performAjaxSearch: function (searchTerm, resultsContainer) {
			var ajaxData = {
				action: 'ufc_search_seeds',
				nonce: ufc_ajax_object.nonce,
				search_term: searchTerm,
			};

			resultsContainer
				.html('<li class="ufc-loading">Searching...</li>')
				.show();

			$.post(ufc_ajax_object.ajax_url, ajaxData, function (response) {
				resultsContainer.empty();
				if (response.success && response.data.length > 0) {
					$.each(response.data, function (index, item) {
						var listItem = $('<li></li>')
							.addClass('ufc-result-item')
							.attr('data-key', item.key)
							.text(item.name);
						resultsContainer.append(listItem);
					});
				} else {
					resultsContainer.html(
						'<li class="ufc-no-results">No matches found.</li>'
					);
				}
				resultsContainer.show();
			});
		},

		/**
		 * Handle a click on a search result item.
		 * @param {Event} e The click event.
		 */
		handleResultClick: function (e) {
			var clickedItem = $(e.currentTarget);
			var form = clickedItem.closest('form');
			var seedKey = clickedItem.data('key');
			var seedName = clickedItem.text();

			form.find('.ufc-plant-search').val(seedName);
			form.find('.ufc-plant-type-key').val(seedKey);
			form.find('.ufc-search-results').empty().hide();
		},

		/**
		 * Handle the submission of any calculator form.
		 * @param {Event} e The submit event.
		 */
		handleFormSubmit: function (e) {
			e.preventDefault();
			var form = $(e.currentTarget);
			var resultWrapper = form
				.closest('.ufc-calculator-wrapper')
				.find('.ufc-result-wrapper, .ufc-result'); // Target both result wrappers

			// For seed calculator, ensure a plant was selected.
			var plantTypeInput = form.find('.ufc-plant-type-key');
			if (plantTypeInput.length > 0 && !plantTypeInput.val()) {
				resultWrapper.html(
					'<div class="ufc-result ufc-error">Error: Please select a plant from the search results.</div>'
				);
				return;
			}

			// Add our security nonce to the form data.
			var formData = form.serialize() + '&nonce=' + ufc_ajax_object.nonce;

			resultWrapper.html(
				'<div class="ufc-result ufc-calculating">Calculating...</div>'
			);

			$.post(ufc_ajax_object.ajax_url, formData, function (response) {
				if (response.success) {
					// The magic happens here: we just inject the HTML from the server.
					resultWrapper.html(response.data.html);
				} else {
					var message =
						response.data.message || 'An unknown error occurred.';
					resultWrapper.html(
						'<div class="ufc-result ufc-error">Error: ' +
							message +
							'</div>'
					);
				}
			});
		},

		/**
		 * Handle unit change in the mulch calculator.
		 * @param {Event} e The change event.
		 */
		handleMulchUnitChange: function (e) {
			var radio = $(e.currentTarget);
			var wrapper = radio.closest('.ufc-calculator-wrapper');
			var areaLabel = radio.data('area-label');
			var depthLabel = radio.data('depth-label');

			wrapper.find('.ufc-area-unit').text(areaLabel);
			wrapper.find('.ufc-depth-unit').text(depthLabel);
		},
	};

	ufcCalculators.init();
});
