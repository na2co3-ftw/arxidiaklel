// 正俗混合表示
function lozsins(axn, ism){
	if(!axn)
		return ism;
	if(!ism)
		return axn;

	axn = axn.split("\n");
	ism = ism.split("\n");
	if (!/\S/.test(axn.slice(-1)))
		axn.pop();
	if (!/\S/.test(ism.slice(-1)))
		ism.pop();

	var sam = [];
	var i, j;

	// タグを分析
	var axn_atap = [];
	var axn_esteltook = axn.length;
	var axn_sevkit = axn.length;
	for (i = 0; i < axn.length; i++) {
		if (/^(［.*］)./.test(axn[i])) {
			axn_atap[i] = axn[i].match(/^(［.*］)./)[1];
		} else {
			// 正幻の一行目はタグ無しでも訳語欄として扱う
			if (i != 0) {
				axn_esteltook = i;
				break;
			}
		}
	}
	for (i = axn_esteltook; i < axn.length; i++) {
		if (/^(［.*］|【.*】)$/.test(axn[i])) {
			axn_sevkit = i;
			break;
		}
	}

	var ism_atap = [];
	var ism_esteltook = ism.length;
	var ism_sevkit = ism.length;
	for (i = 0; i < ism.length; i++) {
		if (/^(［.*］)./.test(ism[i])) {
			ism_atap[i] = ism[i].match(/^(［.*］)./)[1];
		} else {
			ism_esteltook = i;
			break;
		}
	}
	for (i = ism_esteltook; i < ism.length; i++) {
		if (/^(［.*］|【.*】)$/.test(ism[i])) {
			ism_sevkit = i;
			break;
		}
	}

	// ユマナ語源欄を探す
	var vetemo_minamis = /^[-a-z0-9制古赤初中先定高恣＠]/;
	var axn_vetemo = -1;
	for (i = axn_esteltook; i < axn_sevkit; i++) {
		if (vetemo_minamis.test(axn[i])) {
			// カルディア語源欄だった場合、次の行がユマナ語源欄になる
			if ((axn[i].indexOf(";") >= 0 || /[制古赤初中先定高]/.test(axn[i + 1])) && vetemo_minamis.test(axn[i + 1]))
				axn_vetemo = i + 1;
			else
				axn_vetemo = i;
			break;
		}
	}

	var ism_vetemo = -1;
	for (i = ism_esteltook; i < ism_sevkit; i++) {
		if (vetemo_minamis.test(ism[i])) {
			// カルディア語源欄だった場合、次の行がユマナ語源欄になる
			if ((ism[i].indexOf(";") >= 0 || /[制古赤初中先定高]/.test(ism[i + 1])) && vetemo_minamis.test(ism[i + 1]))
				ism_vetemo = i + 1;
			else
				ism_vetemo = i;
			break;
		}
	}


	// 訳語欄を出力
	var xiatap = ["［類義語］", "［反意語］", "［類音］", "［レベル］"]; // 劣位タグ（後回しにされるタグ）
	for (i = 0; i < axn_esteltook; i++) {
		if (xiatap.indexOf(axn_atap[i]) >= 0) {
			// 正側が劣位タグに達したら、その前に俗側の優位タグをすべて出力する
			for (j = 0; j < ism_esteltook; j++) {
				if (ism[j] != "%AXTES%" && xiatap.indexOf(ism_atap[j]) < 0) {
					sam.push(ism[j]);
					ism[j] = "%AXTES%";
				}
			}
			// 正側の劣位タグより先に出力されるべき俗側の劣位タグを出力する
			for (j = 0; j < ism_esteltook; j++) {
				if (ism[j] != "%AXTES%" &&
					xiatap.indexOf(axn_atap[i]) > xiatap.indexOf(ism_atap[j])) {
					sam.push(ism[j]);
					ism[j] = "%AXTES%";
				}
			}
		}

		// 正側を出力
		sam.push(axn[i]);
		// 俗側に正側とタグが一致する項目があればまとめて出力する
		for (j = 0; j < ism_esteltook; j++) {
			if (ism[j] != "%AXTES%" && axn_atap[i] == ism_atap[j]) {
				if (axn_atap[i] != "［レベル］") {
					// レベルは正側のみ表示
					sam.push(ism[j]);
				}
				ism[j] = "%AXTES%";
			}
		}
	}
	// 俗側の残りを出力
	for (i = 0; i < ism_esteltook; i++) {
		if(ism[i] != "%AXTES%") {
			sam.push(ism[i]);
			ism[i] = "%AXTES%";
		}
	}


	// 語源欄の前にあるものを出力
	var axn_kes = axn_vetemo != -1 ? axn_vetemo : axn_sevkit;
	var ism_kes = ism_vetemo != -1 ? ism_vetemo : ism_sevkit;
	for (i = axn_esteltook; i < axn_kes; i++) {
		sam.push(axn[i]);
	}
	for (i = ism_esteltook; i < ism_kes; i++) {
		sam.push(ism[i]);
	}

	// 語源欄の出力
	var ism_salt;
	if (axn_vetemo != -1) {
		if (ism_vetemo != -1) {
			// 正俗両側に語源欄がある場合
			ism_salt = ism[ism_vetemo].match(/^＠?(\d+)/);
			// 単なる立項者の記録でない限り俗優先
			if (!ism_salt || ism_salt[1] < 24) {
				sam.push(ism[ism_vetemo]);
			} else {
				sam.push(axn[axn_vetemo]);
			}
		} else {
			// 正側だけに語源欄がある場合、正側を出力
			sam.push(axn[axn_vetemo]);
		}
	} else if (ism_vetemo != -1) {
		// 俗側だけに語源欄がある場合、俗側を出力
		sam.push(ism[ism_vetemo]);
	}

	// 語源欄の後にあるものを出力
	if (axn_vetemo != -1) {
		for (i = axn_vetemo + 1; i < axn_sevkit; i++) {
			sam.push(axn[i]);
		}
	}
	if (ism_vetemo != -1) {
		for (i = ism_vetemo + 1; i < ism_sevkit; i++) {
			sam.push(ism[i]);
		}
	}


	// 文章部を出力
	//［］タグ
	var atap;
	i = axn_sevkit;
	while (/^［.*］$/.test(axn[i])) {
		atap = axn[i];
		// 正側を出力
		sam.push(axn[i]);
		i++;
		for (; i < axn.length; i++) {
			if (/^(［.*］|【.*】)$/.test(axn[i]))
				break;
			sam.push(axn[i]);
		}

		// 俗側に同じタグがあれば続けて俗を出力
		j = ism_sevkit;
		while (j < ism.length) {
			if (atap == ism[j]) {
				ism[j] = "%AXTES%";
				j++;
				for (; j < ism.length; j++) {
					if (/^(［.*］|【.*】|%AXTES%)$/.test(ism[j]))
						break;
					sam.push(ism[j]);
					ism[j] = "%AXTES%";
				}
			} else {
				j++;
			}
		}
	}
	// 俗側の残りを出力
	var epi = 1;
	for (j = ism_sevkit; j < ism.length; j++) {
		if (epi && /^【.*】$/.test(ism[j])) {
			epi = 0;
		} else if (!epi && /^［.*］$/.test(ism[j])) {
			epi = 1;
		}
		if (epi && ism[j] != "%AXTES%") {
			sam.push(ism[j]);
			ism[j] = "%AXTES%";
		}
	}

	//【】タグ
	while (/^【.*】$/.test(axn[i])) {
		atap = axn[i];
		// 正側を出力
		sam.push(axn[i]);
		i++;
		for (; i < axn.length; i++) {
			if (/^【.*】$/.test(axn[i]))
				break;
			sam.push(axn[i]);
		}

		// 俗側に同じタグがあれば続けて俗を出力
		j = ism_sevkit;
		while (j < ism.length) {
			if (atap == ism[j]) {
				ism[j] = "%AXTES%";
				j++;
				for (; j < ism.length; j++) {
					if (/^(［.*］|【.*】|%AXTES%)$/.test(ism[j]))
						break;
					sam.push(ism[j]);
					ism[j] = "%AXTES%";
				}
			} else {
				j++;
			}
		}
	}
	// 俗側の残りを出力
	for (j = ism_sevkit; j < ism.length; j++) {
		if(ism[j] != "%AXTES%") {
			sam.push(ism[j]);
			ism[j] = "%AXTES%";
		}
	}

	sam.push("");
	return sam.join("\n");
}

module.exports = lozsins;
