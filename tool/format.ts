
type Dictionary = Array<{[lang: string]: Entry}>;

interface Entry {
	word: string,

	pron?: string,

	defs: Array<{
		tags: Array<string>,
		trans: Array<string>,
		desc?: string
	}>,

	rel: Array<{
		tags: Array<string>,
		words: Array<string>
	}>,


	ety_atolas?: string,

	ety?: string,

	otherlang?: string,

	tags: Array<string>,

	details: Array<{
		tag: string,
		text: string
	}>,

	example: Array<{
		tag: string,
		texts: Array<string>
	}>,

	image?: string
}
