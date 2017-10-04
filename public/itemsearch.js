/**
 * Created by Smiley on 09.07.2017.
 */

'use strict';

class GW2ItemSearch{

	/**
	 * @param form
	 */
	constructor(form){
		this.form = $(form);
		this.url = 'itemsearch.php';
		this.search = $('search');
		this.resultlist = $('resultlist');
		this.details = $('details');
		this.pagination = $('pagination');
		this.item_type = $('type');
		this.item_subtype = $('subtype');
		this.item_attributes = $('attributes');

		this.populateForm();

		new Form.Element.Observer(this.search, 1.0, () => this.itemSearch());

		$$('.options').each(e => new Form.Element.Observer(e, 1.0, () => this.itemSearch()));

		this.form.observe('submit', ev => {
			Event.stop(ev);
			this.itemSearch();
		});

		$('reset').observe('click', ev => {
			Event.stop(ev);
			this.form.reset();
			this.resultlist.update('');
			this.details.update('');
			this.pagination.update('');
		});
	}

	/**
	 *
	 */
	populateForm(){

		new Ajax.Request(this.url, {
			method: 'post',
			parameters: {load: 'form'},
			onSuccess: response => {
				let r = response.responseJSON;

				// add attribute combinations
				r.combinations.each(a => this.item_attributes.insert(
					new Element('option', {'value': a.id}).update(a.attributes.join(' - '))
				));

				// populate the types select
				r.types.each(t => this.item_type.insert(
					new Element('option', {'value': t}).update(t)
				));

				// automagically change the subtype select
				this.item_type.observe('change', ev => {

					if(r.subtypes[ev.target.value]){

						this.item_subtype.update(new Element('option', {'value': ''}).update('-- subtype --'));

						r.subtypes[ev.target.value].each(st => {

							if(st.length){
								this.item_subtype.insert(new Element('option', {'value': st}).update(st));
							}

						});

					}

				});

			}
		});

	}

	/**
	 * @param page
	 */
	itemSearch(page){
		page = page || 1;

		let params = {};
		let formdata = {
			form: this.form.serialize(true),
			p: page
		};

		let chatlinks = this.search.value.match(/(\[&([a-z\d+\/]+=*)])/gi);

		if(chatlinks){
			formdata.matches = chatlinks;
			params.chatlinks = Object.toJSON(formdata);
		}
		else{
			// base64_encode the search string to not break umlauts, accented chars etc.
			formdata.str = GW2ItemSearch.base64_encode(this.search.value);
			params.search = Object.toJSON(formdata);
		}

		new Ajax.Request(this.url, {
			method: 'post',
			parameters: params,
			onSuccess: response => {
				let r = response.responseJSON;

				this.resultlist.childElements().invoke('stopObserving');
				this.resultlist.update('');
				r.data.each(item => this.resultlist.insert(this.listItem(item)));

				this.pagination.childElements().invoke('stopObserving');
				this.pagination.update(r.pagination);
				this.pagination.select('.p-links:not(.p-current)').invoke('observe', 'click', ev => {
					Event.stop(ev);
					this.itemSearch(ev.target.dataset.page);
				});

			}
		});
	}

	/**
	 * @param item
	 * @returns {void|*}
	 */
	listItem(item){
//		console.log(item);

		return new Element('div', {'data-id': item.id, 'class': item.rarity.toLowerCase()})
			.update(item.name)
			.observe('click', ev => {
				this.resultlist.select('.selected').invoke('removeClassName', 'selected');
				ev.target.addClassName('selected');

				new Ajax.Request(this.url, {
					method: 'post',
					parameters: {
						details: Object.toJSON({
							id: item.id,
							lang: $F('lang'),
						})
					},
					onSuccess: response => {
						let r = response.responseJSON;

						$$('.selectable').invoke('stopObserving');

						this.details.update(r.html);

						$$('.selectable').invoke('observe', 'click', ev => {
							ev.target.select();
						});

					}

				});

			})

	}


	/**
	 * @link http://phpjs.org/functions/base64_encode/
	 *
	 * @param data
	 * @returns {*}
	 */
	static base64_encode(data){
		let b64 = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=';
		let o1, o2, o3, h1, h2, h3, h4, bits, i = 0, ac = 0, tmp_arr = [];

		if(!data){
			return data;
		}
		do{ // pack three octets into four hexets
			o1 = data.charCodeAt(i++);
			o2 = data.charCodeAt(i++);
			o3 = data.charCodeAt(i++);
			bits = o1<<16 | o2<<8 | o3;
			h1 = bits>>18 & 0x3f;
			h2 = bits>>12 & 0x3f;
			h3 = bits>>6 & 0x3f;
			h4 = bits & 0x3f;
			// use hexets to index into b64, and append result to encoded string
			tmp_arr[ac++] = b64.charAt(h1)+b64.charAt(h2)+b64.charAt(h3)+b64.charAt(h4);
		}
		while(i < data.length);

		let enc = tmp_arr.join('');
		let r = data.length%3;

		return (r ? enc.slice(0, r-3) : enc)+'==='.slice(r || 3);
	}

}
