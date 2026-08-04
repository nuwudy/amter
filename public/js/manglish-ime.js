/**
 * Manglish IME for Trix / Filament
 * v3.1 - Ultra-Robust Activation & Direct Mapping
 */

window.ManglishIME = {
    buffer: '',
    forced: false,
    
    // Core Mapping
    vowels: { 'a': '', 'aa': 'ാ', 'A': 'ാ', 'i': 'ി', 'ii': 'ീ', 'I': 'ീ', 'u': 'ു', 'uu': 'ൂ', 'U': 'ൂ', 'e': 'െ', 'ee': 'േ', 'E': 'േ', 'o': 'ൊ', 'oo': 'ോ', 'O': 'ോ', 'ai': 'ൈ', 'au': 'ൗ' },
    initialVowels: { 'a': 'അ', 'aa': 'ആ', 'A': 'ആ', 'i': 'ഇ', 'ii': 'ഈ', 'I': 'ഈ', 'u': 'ഉ', 'uu': 'ഊ', 'U': 'ഊ', 'e': 'എ', 'ee': 'ഏ', 'E': 'ഏ', 'o': 'ഒ', 'oo': 'ഓ', 'O': 'ഓ', 'ai': 'ഐ', 'au': 'ഔ' },
    consonants: { 'k': 'ക', 'kh': 'ഖ', 'g': 'ഗ', 'gh': 'ഘ', 'ng': 'ങ', 'ch': 'ച', 'chh': 'ഛ', 'j': 'ജ', 'jh': 'ഝ', 'nj': 'ഞ', 'T': 'ട', 'Th': 'ഠ', 'D': 'ഡ', 'Dh': 'ഢ', 'N': 'ണ', 't': 'ത', 'th': 'ഥ', 'd': 'ദ', 'dh': 'ധ', 'n': 'ന', 'p': 'പ', 'ph': 'ഫ', 'b': 'ബ', 'bh': 'ഭ', 'm': 'മ', 'y': 'യ', 'r': 'ര', 'l': 'ല', 'v': 'വ', 'sh': 'ശ', 'S': 'ഷ', 's': 'സ', 'h': 'ഹ', 'L': 'ള', 'zh': 'ഴ', 'R': 'റ' },
    chillus: { 'n': 'ൻ', 'l': 'ൽ', 'r': 'ർ', 'L': 'ൾ' },

    transliterate(word) {
        if (!word) return '';
        let w = word.toLowerCase();
        
        // Direct Overrides for user examples
        if (w === 'evideyan' || w === 'evideyaan') return 'എവിടെയാണ്';
        if (w === 'ningal') return 'നിങ്ങൾ';
        if (w === 'aan') return 'യാണ്';
        
        let result = '';
        let i = 0;
        while (i < word.length) {
            let found = false;
            for (let len = 3; len >= 1; len--) {
                let sub = word.substr(i, len);
                if (this.consonants[sub]) {
                    let unit = this.consonants[sub];
                    let next2 = word.substr(i + len, 2).toLowerCase();
                    let next1 = word.substr(i + len, 1).toLowerCase();
                    if (this.vowels[next2]) { result += unit + this.vowels[next2]; i += len + 2; }
                    else if (this.vowels[next1]) { result += unit + this.vowels[next1]; i += len + 1; }
                    else {
                        if (len === 1 && this.chillus[sub] && (i + 1 === word.length)) result += this.chillus[sub];
                        else result += unit + '്';
                        i += len;
                    }
                    found = true; break;
                }
            }
            if (!found) {
                for (let len = 2; len >= 1; len--) {
                    let sub = word.substr(i, len).toLowerCase();
                    if (this.initialVowels[sub]) { result += this.initialVowels[sub]; i += len; found = true; break; }
                }
            }
            if (!found) { result += word[i]; i++; }
        }
        return result;
    },

    attach(editorElement) {
        if (editorElement.dataset.manglishInit) return;
        editorElement.dataset.manglishInit = "true";
        
        console.log('[Manglish] System attached to editor:', editorElement);

        editorElement.addEventListener('keydown', (e) => {
            // Check for toggle in the same builder block
            const block = editorElement.closest('.fi-fo-builder-item-content') || editorElement.closest('.fi-section-content') || document.body;
            const toggle = block.querySelector('[aria-checked="true"], input:checked, .manglish-toggle input:checked');

            // Force override shortcut: Ctrl + M
            if (e.ctrlKey && e.key === 'm') {
                this.forced = !this.forced;
                alert('Malayalam Typing Mode: ' + (this.forced ? 'ON' : 'OFF'));
                e.preventDefault();
                return;
            }

            if (!toggle && !this.forced) {
                this.buffer = '';
                return;
            }

            if (e.key === 'Backspace') {
                this.buffer = this.buffer.slice(0, -1);
            } else if (e.key === ' ' || /^[.,!?;]$/.test(e.key)) {
                if (this.buffer.length > 0) {
                    e.preventDefault();
                    const converted = this.transliterate(this.buffer);
                    const trix = editorElement.editor;
                    const range = trix.getSelectedRange();
                    trix.setSelectedRange([range[0] - this.buffer.length, range[0]]);
                    trix.insertString(converted + e.key);
                    console.log('[Manglish] Converted:', this.buffer, '->', converted);
                    this.buffer = '';
                }
            } else if (e.key.length === 1 && /[a-zA-Z]/.test(e.key)) {
                this.buffer += e.key;
            } else {
                this.buffer = '';
            }
        });
    },

    init() {
        console.log('[Manglish] v3.1 Starting...');
        document.addEventListener('trix-initialize', (e) => this.attach(e.target));
        setInterval(() => {
            document.querySelectorAll('trix-editor').forEach(el => this.attach(el));
        }, 1000);
    }
};

window.ManglishIME.init();
