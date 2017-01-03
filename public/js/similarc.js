var Droplet = function(word, value){
  this.word = word;
  this.value = value;
  this.matchcount = 0;
}

function shower(word, arr, val){
  if(arr == "num"){
    if(!isNaN(word)){
      d = new Droplet(word, val);
      return d;
    }
    return false;
  }
  for(k = 0; k < arr.length; k++){
    if(word == arr[k] || word == (arr[k] + "s")){
      d = new Droplet(word, val);
      return d;
    }
  }
  return false;
}

var Similarc = function(original) {
  original = original.toLowerCase();
  this.original = original;
  highdensity = ["question", "problem", "number"];
  this.sentences = original.split(". "); // splitting by paragraph length;
  for(i = 0; i < this.sentences.length; i++){
    words = this.sentences[i].split(" "); //split per words
    for(j = 0; j < words.length; j++){
      if(shower(words[j], highdensity, 10) != false){
        words[j] = shower(words[j], highdensity, 10);
        continue; // found and continue;
      }
      if(shower(words[j], "num", 8) != false){
        words[j] = shower(words[j], "num", 8);
        continue; // found and continue;
      }
      words[j] = new Droplet(words[j], 1);
    }
    this.sentences[i] = words;
  }
}


function runner(i, j, asen, bsen, finiteness = false){
  if(!asen[i]) { return 0; }
  if(!bsen[j]) { return 0; }
  console.log("passedblock 1");

  if(asen[i].word != bsen[j].word && finiteness == false){
    return 0;
  }
  //implies they are the same.
  console.log("passedblock 2");
  matchness = 0;
  if(
    asen[i + 1] && bsen[j + 1] && asen[i + 1].word == bsen[j + 1].word ||
    asen[i - 1] && bsen[j - 1] && asen[i - 1].word == bsen[j - 1].word
  ) {
    if(
        asen[i + 2] && bsen[j + 2] &&  asen[i + 2].word == bsen[j + 2].word ||
        asen[i - 2] && bsen[j - 2] &&  asen[i - 2].word == bsen[j - 2].word
    ) {
      if(
        asen[i + 3] && bsen[j + 3] && asen[i + 3].word == bsen[j + 3].word ||
        asen[i - 3] && bsen[j - 3] && asen[i - 3].word == bsen[j - 3].word
      ) {
        if(asen[i + 3] && bsen[j + 3] && asen[i - 3] && bsen[j - 3]){
          console.log("here3");
          return 64 + 3.4 * Math.max(asen[i + 3].value, asen[i - 3].value, bsen[j + 3].value, bsen[j - 3].value);
        } else {
          return 64;
        }
      }
      if(asen[i + 2] && bsen[j + 2] && asen[i - 2] && bsen[j - 2]){
        return 53 + 4.1 * Math.max(asen[i + 2].value, asen[i - 2].value, bsen[j + 2].value, bsen[j - 2].value);
      } else {
        return 56;
      }
    }
    if(asen[i + 1] && bsen[j + 1] && asen[i - 1] && bsen[j - 1]){
      return 55 + 4.2 * Math.max(asen[i + 1].value, asen[i - 1].value, bsen[j + 1].value, bsen[j - 1].value);
    } else {
      return 55;
    }
  }
  if(finiteness == true){ return 53; } //avoids infinite recursion
  c = runner(i + 1, j, asen, bsen, true);
  v = runner(i, j + 1, asen, bsen, true);
  n = runner(i - 1, j, asen, bsen, true);
  m = runner(i, j - 1, asen, bsen, true);
  return Math.max(c, v, n, m, 55);
}

function compareSimilarc(a_str, b_str){
  if(a_str.sentences == b_str.sentences){
    return 100; //sliding scale from 100 to 0
  }
  asen = a_str.sentences[0];
  console.log("b_str: " + b_str);
  bsen = b_str.sentences[0];
  vals = [];
  for(y = 0; y < asen.length; y++){
    matchesness = [];
    for(r = 0; r < bsen.length;r++){
      console.log("r: " + r);
      console.log(asen[y]);
      console.log(bsen[r]);
      console.log(runner(y, r, asen, bsen));
      matchesness.push(runner(y, r, asen, bsen));
    }
    vals[y] = Math.max.apply(null, matchesness);
  }
  count = 0;
  tot = 0;
  for(i = 0; i < vals.length; i++){
    if(vals[i] != 0){
      count++;
      console.log(vals);
      tot = tot + vals[i];
    } else {
      count ++;
      tot = tot + 46.5; //leverage
    }
  }
  if(count == 0){ return 0; }
  console.log(tot);
  console.log(count);
  return tot / count; // average
}



s = new Similarc("Hello, this is number 1 and number 23 power gradient isajd aisdj .");
a = new Similarc("hi his asidj asdij aasdij .");
console.log(compareSimilarc(s, a));
