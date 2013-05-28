"use strict";

(function () {
	L.TextMarkers = {};

	L.TextMarkers.Icon = L.Icon.extend({
		options: {
			iconSize: [25, 41],
			iconAnchor: [12, 41],
			popupAnchor: [1, -34],

			shadowSize: [41, 41],
			text: ''

		},

		_getIconUrl: function (name) {
			var key = name + 'Url';

			if (this.options[key]) {
				return this.options[key];
			}

			if (L.Browser.retina && name === 'icon') {
				name += '-2x';
			}

			var path = L.Icon.Default.imagePath;

			if (!path) {
				throw new Error('Couldn\'t autodetect L.TextMarkers.Default.imagePath, set it manually.');
			}

			return path + '/marker-' + name + '.png';
		},


		initialize: function (options) {
			options = L.setOptions(this, options);
		},

		createIcon: function () {
			var div = document.createElement('div'),
				options = this.options,
				iconurl = this._getIconUrl("icon");

			div.innerHTML = '<img src="' + iconurl + '" class="leaflet-marker-icon" alt="Icon"><div class="text">' + options.text + '</div>';

			this._setIconStyles(div, 'textmarker');
			return div;
		}
	});

	L.TextMarkers.icon = function (options) {
		return new L.TextMarkers.Icon(options);
	};

}());


