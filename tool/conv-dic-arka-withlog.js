const fs = require("fs");
const lozsins = require("./lozsins");
const parseArkaEntry = require("./parse-arka-entry");

const EDITOR = "titlil gas (bot)";
const NOW = Math.floor(Date.now() / 1000);

function parseDate(str) {
	let match = str.match(/(\d+)\/(\d+)\/(\d+) (\d+):(\d+):(\d+)/);
	if (match === null) {
		console.log(`invalid date: ${str}`);
		return 0;
	}
	return Date.UTC(
		parseInt(match[1]),
		parseInt(match[2]) - 1,
		parseInt(match[3]),
		parseInt(match[4]) - 9, // JST
		parseInt(match[5]),
		parseInt(match[6])
	) / 1000;
}

let log = [];

let ismlog = [];
let axnlog = [];
let bbslog = [];
let axndic = fs.readFileSync("data/arka.dat", "utf8").split("\r\n");
for (let entry of axndic) {
	if (!entry) {
		continue;
	}
	let [word, exp] = entry.split(" ///  / ");
	log.push([{editor: "seren", fmt: "ism", content: {word, axn: exp.replace(/ \\ /g, "\n")}}]);
}

let zip = new require("node-zip")(fs.readFileSync("data/arka-kaxk.zip"));
for (let filepath of Object.keys(zip.files)) {
	if (filepath.slice(-3) != "txt"){
		continue;
	}
	let file = zip.files[filepath];
	filepath = filepath.split("/");
	let ivl = filepath[0] == "ivl";
	let name = filepath[1];
	let bbs = !ivl && name == ".txt";
	if (!name) {
		continue;
	}

	let data = file.asText();
	let records = [];
	let prevRecord = null;
	for (let kiis of data.split("\n")) {
		if (!kiis) continue;
		kiis = kiis.split("\t");
		let [dateStr, editor, type, exp, coment, word, original] = kiis;
		let date;
		if (bbs) {
			coment = kiis.slice(4).join("\t");
			records.push({date: parseDate(dateStr), editor, type, coment});
		}
		if (prevRecord) {
			if (!date) {
				date = prevRecord.date;
			}
			if (type != "lad" && type != "luul" && type != "ret") {
				word = prevRecord ? prevRecord.word : name.slice(0, -4);
			}
		}
		if (!date) {
			date = parseDate(dateStr);
		}
		if (type == "sedo" || type == "yuki") {
			exp = "";
		}
		exp = exp.replace(/ \\ /g, "\n");
		coment = coment.replace(/ \\ /g, "\n");
		let record = {date, editor, type, exp, coment, word};
		if (ivl) {
			record.original = original;
		}
		records.push(record);
		prevRecord = record;
	}
	if (bbs) {
		bbslog = records;
	} else if (ivl) {
		axnlog.push([records, name]);
	} else {
		ismlog.push([records, name]);
	}
}
console.log("1/4");

axnlog.forEach(([data, filename], count) => {
	let entry;
	for (let line of data) {
		let {date, editor, type, exp, coment, word, original} = line;
		if (!entry) {
			entry = seachEntry(log, date, original || word);
			if (entry === null) {
				console.log(`not found entry "${original || word}" "ivl/${filename}"`);
			}
		}
		if (entry) {
			if (type == "rens") {
				entry.push({date, editor, coment});
			} else {
				entry.push({date, editor, content: {word, axn: exp}, coment});
			}
		}
	}
	if (count % 500 == 0) {
		console.log(`2/4 ${count}/${axnlog.length}`);
	}
});
console.log(`2/4 ${axnlog.length}/${axnlog.length}`);

ismlog.forEach(([data, filename], count) => {
	let entry;
	data.forEach(line => {
		let {date, editor, type, exp, coment, word} = line;
		let curEntry = seachEntry(log, date, word);
		if (!entry) {
			entry = curEntry;
		} else if (entry != curEntry) {
			if (searchAxnRecordIndex(entry, date) != null || curEntry) {
				let prev = searchRecordIndex(entry, date);
				if (prev == -1) {
					console.log(`invalid date "${date}" "kaxk/${filename}"`);
					prev = 0;
				}
				let prevTags = getRecordTags(entry, date);
				if (prevTags.includes("request")) {
					entry.splice(prev + 1, 0, {date, editor, coment, tags: ["removed"]});
				} else if (!prevTags.includes("removed")) {
					entry.splice(prev + 1, 0, {date, editor, content: {ism: ""}, coment});
				}
				entry = curEntry;
			}
		}
		let record;
		if (type == "rens") {
			record = {date, editor, coment};
		} else if (type == "ret") {
			record = {date, editor, fmt: "text", content: {text: word}, coment, tags: ["request"]};
		} else if (type == "yuki") {
			record = {date, editor, coment, tags: ["removed"]};
		} else {
			record = {date, editor, content: {word, ism: exp}, coment};
		}
		if (entry) {
			let prev = searchRecordIndex(entry, date);
			entry.splice(prev + 1, 0, record);
		} else {
			if (!record.fmt) {
				record.fmt = "ism";
			}
			entry = [record];
			log.push(entry);
		}
	});
	if (count % 500 == 0) {
		console.log(`3/4 ${count}/${ismlog.length}`);
	}
});
console.log(`3/4 ${ismlog.length}/${ismlog.length}`);

for (let entry of log) {
	let word, axn, ism, fmt;
	let tags = [];
	for (let record of entry) {
		if (record.coment === "") {
			delete record.coment;
		}
		if (record.fmt) {
			fmt = record.fmt;
			word = "";
			axn = "";
			ism = "";
		}
		if (fmt != "ism" || !record.content) {
			if (record.tags) {
				tags = record.tags;
			}
			continue;
		}
		if (record.content.hasOwnProperty("word")) {
			if (word == record.content.word) {
				delete record.content.word;
			} else {
				word = record.content.word;
			}
		}
		if (record.content.hasOwnProperty("axn")) {
			if (axn == record.content.axn) {
				delete record.content.axn;
			} else {
				axn = record.content.axn;
			}
		}
		if (record.content.hasOwnProperty("ism")) {
			if (ism == record.content.ism) {
				delete record.content.ism;
			} else {
				ism = record.content.ism;
			}
		}
		if (axn == "" && ism == "") {
			delete record.content;
			if (tags[0] != "removed") {
				record.tags = tags = ["removed"];
			}
		} else if (tags.length) {
			record.tags = tags = [];
		}
	}
	if (!tags.length && word) {
		let content = parseArkaEntry(word, lozsins(axn, ism));
		entry.push({date: NOW, editor: EDITOR, fmt: "arx", content});
	}
}
console.log("4/4");

fs.writeFileSync("data/arka.ldjson", log.map(entry => JSON.stringify(entry)).join("\n"));

function seachEntry(log, date, word) {
	let entries = log.filter(entry => {
		let i = searchRecordIndex(entry, date);
		while (i >= 0) {
			if (entry[i].content && entry[i].content.word) {
				return entry[i].content.word == word;
			}
			i--;
		}
		return false;
	});
	if (entries.length != 1) {
		return null;
	}
	return entries[0];
}

function searchRecordIndex(entry, date) {
	let i;
	for (i = 0; i < entry.length; i++) {
		if (entry[i].date && entry[i].date > date) {
			break;
		}
	}
	return i - 1;
}

function searchAxnRecordIndex(entry, date) {
	let ret = null;
	for (let i = 0; i < entry.length; i++) {
		if (!entry[i].content || !entry[i].content.axn) {
			continue;
		}
		if (entry[i].date && entry[i].date > date) {
			break;
		}
		ret = i;
	}
	return ret;
}

function getRecordTags(entry, date) {
	let tags = [];
	for (let i = 0; i < entry.length; i++) {
		if (!entry[i].hasOwnProperty("tags")) {
			continue;
		}
		if (entry[i].date && entry[i].date > date) {
			break;
		}
		tags = entry[i].tags;
	}
	return tags;
}
