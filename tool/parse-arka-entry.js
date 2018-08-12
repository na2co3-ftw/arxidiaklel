function parseArkaEntry(word, exp) {
	let entry = {
		word: word.replace(/\(\d+\)$/, ""),
		defs: [],
		rel: [],
		//ety_atolas
		//ety
		//otherlang
		tags: [],
		details: [],
		example: []
		//image
	};

	if (word.match(/,\S|[^ -~]/)) {
		return null;
	}

	exp = exp.split("\n");
	if (exp[exp.length - 1] == "") {
		exp = exp.slice(0, -1);
	}
	let i = 0;
	while (i < exp.length) {
		let line = exp[i];
		if (line.substr(0, 1) != "［") break;
		let tags = [];
		let match;
		while ((match = line.match(/^［(.*?)］/)) !== null) {
			tags.push(match[1]);
			line = line.substr(match[0].length);
		}

		if (tags.some(tag => ["類義語", "反意語", "類音"].includes(tag))) {
			let rel = {
				tags,
				words: line.split("、")
			};
			entry.rel.push(rel);
		} else if (tags.includes("レベル")) {
			entry.tags.push("レベル" + (line.charCodeAt(0) - "０".charCodeAt(0)));
		} else {
			let def = {
				tags
			};
			if ((match = line.match(/[。:→←].*$/)) !== null) {
				def.desc = match[0].replace(/^。/, "");
				line = line.substr(0, match.index);
			}
			if ((match = line.match(/^（[a-z].*?）/)) !== null) {
				def.desc = match[0] + (def.desc || "");
				line = line.substr(match[0].length);
			}
			def.trans = line.split("、");
			entry.defs.push(def);
		}
		i++;
	}

	if (i < exp.length && exp[i].includes(";")) {
		entry.ety_atolas = exp[i];
		i++;
	}

	if (i < exp.length) {
		entry.ety = exp[i];
		i++;
	}

	while (i < exp.length) {
		let line = exp[i];
		if (line.substr(0, 1) == "［" || line.substr(0, 1) == "【") break;
		entry.otherlang = (entry.otherlang ? entry.otherlang + "\n" : "") + line;
		i++;
	}

	while (i < exp.length) {
		if (exp[i].substr(0, 1) != "［") break;
		let tag = exp[i].match(/［(.*?)］/)[1];
		i++;
		let detail = {
			tag,
			text: ""
		};
		while (i < exp.length) {
			if (exp[i].substr(0, 1) == "［" || exp[i].substr(0, 1) == "【") break;
			detail.text += exp[i] + "\n";
			i++;
		}
		detail.text = detail.text.replace(/\n+$/g, "");
		entry.details.push(detail);
	}

	while (i < exp.length) {
		if (exp[i].substr(0, 1) != "【") break;
		let tag = exp[i].match(/【(.*?)】/)[1];
		i++;
		if (tag.includes("画像")) {
			entry.image = word + ".jpg";
			break;
		}
		let example = {
			tag,
			texts: []
		};
		while (i < exp.length) {
			if (exp[i].substr(0, 1) == "【") break;
			example.texts.push(exp[i]);
			i++;
		}
		entry.example.push(example);
	}

	return entry;
}

module.exports = parseArkaEntry;
