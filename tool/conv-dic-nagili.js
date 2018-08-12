const fs = require("fs");
const csvParse = require("csv").parse;

csvParse(fs.readFileSync("data/nagili.csv", "utf8"), {comment: ""}, (_, data) => {
	let ret = [];
	for (let srcEntry of data.slice(1)) {
		let entry = {
			word: srcEntry[0].replace(/\(\d+\)$/, ""),
			defs: [],
			rel: [],
			tags: [],
			details: [],
			example: []
		};

		for (let line of srcEntry[1].split("\n")) {
			if (!line) {
				continue;
			}
			let tags = [];
			let match;
			while ((match = line.match(/^［(.*?)］/)) !== null) {
				tags.push(match[1]);
				line = line.substr(match[0].length);
			}
			while ((match = line.match(/^<(.*?)>|^〈(.*?)〉/)) !== null) {
				tags.push(match[1]);
				line = line.substr(match[1] || match[2]);
			}
			switch (tags[0]) {
				case "発音":
					entry.pron = line;
					break;
				case "類義語":
				case "反意語":
				case "一覧":
				case "同義語":
					entry.rel.push({
						tags,
						words: line.split("、")
					});
					break;
				case "語種":
					if (line.match(/変更あり/)) {
						entry.tags.push("変更あり");
						line = line.substr(0, line.indexOf(" "));
					}
					entry.tags.unshift(line);
					break;
				case "基層":
					entry.details.push({
						tag: "基層",
						text: line
					});
					break;
				case "語源":
					entry.ety = line;
					break;
				case "旧項目":
					entry.rel.push({
						tags: ["旧項目"],
						words: line.split("、")
					});
					break;
				default: {
					entry.defs.push({
						tags,
						trans: line.split("、")
					});
					break;
				}
			}
		}

		let exp = srcEntry[2].split("\n");
		let i = 0;
		while (i < exp.length) {
			if (exp[i].substr(0, 1) != "［") break;
			let tag = exp[i].match(/［(.*?)[］」]/)[1];
			i++;
			let detail = {
				tag,
				text: ""
			};
			while (i < exp.length) {
				if (exp[i].substr(0, 1) == "［") break;
				detail.text += exp[i] + "\n";
				i++;
			}
			detail.text = detail.text.replace(/\n+$/g, "");
			entry.details.push(detail);
		}

		ret.push({"ja": entry});
	}
	fs.writeFileSync("data/nagili.json", JSON.stringify(ret));
});

