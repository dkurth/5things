<?php

class Dictionary {

      static function randomWord($words) {
            shuffle($words);
            return array_slice($words, 0, 1)[0];
      }

      static function saveWord() {
            return Dictionary::randomWord(array(
                  "save",
                  "preserve",
                  "keep",
                  "perpetuate",
                  "retain",
                  "safeguard",
                  "store",
                  "refrigerate",
            ));
      }

      static function deleteWord() {
            return Dictionary::randomWord(array(
                  "delete",
                  "destroy",
                  "expunge",
                  "wipe out",
                  "eliminate",
                  "bleep",
                  "obliterate",
                  "squash",
            ));
      }

      static function weirdWord() {
            return Dictionary::randomWord(array(
                  "rural",
                  "frugal",
                  "twin",
                  "santorum",
                  "detritus",
                  "entrepreneurial",
                  "pronounciation",
                  "moisture",
                  "peculiarly",
                  "crepuscular",
                  "ubiquitous",
                  "malarkey",
                  "bubbles",
                  "dude",
                  "torque",
                  "google",
                  "visualization",
                  "cliche",
                  "floccinaucinihilipilification",
                  "conundrums",
                  "doodlesack",
                  "volunteer",
                  "mississippilessly",
                  "calisthenics",
                  "serendipity",
                  "grommets",
                  "dilettante",
                  "macrosmatic",
                  "supercalifragilisticexpialidocious",
                  "flabergasted",
                  "sphygmomanometer",
                  "Kardashian",
                  "hubbub",
                  "prescription",
                  "caulking",
                  "wreath",
                  "expat",
                  "jentacular",
                  "schnapps",
                  "zdravstvuite",
                  "phlegm",
                  "specific",
                  "quproch",
                  "riboflavin",
                  "cubersome",
                  "garage",
                  "flummoxed",
                  "profiterole",
                  "ruler",
                  "dingleberry",
                  "egg",
                  "ombudsman",
                  "perpendicular",
                  "collate",
                  "abruptly",
                  "word",
                  "bitter",
                  "cellar",
                  "airworthy",
                  "stupendous"
            ));    
    }
}