const fs = require("fs");
const parseArkaEntry = require("./parse-arka-entry");

let textDic = fs.readFileSync("data/arka.dat", "utf8").split("\r\n");
let ret = [];

for (let textEntry of textDic) {
	if (!textEntry) continue;

	let [word, exp] = textEntry.split(" ///  / ");
	let entry = parseArkaEntry(word, exp.replace(" \\ ", "\n"));
	if (!entry) continue;
	ret.push({"ja": entry});
}

fs.writeFileSync("data/arka.json", JSON.stringify(ret));
