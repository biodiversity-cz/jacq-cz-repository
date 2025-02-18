export default function regexValidation() {
    function checkRegexMatch(inputValue) {
        document.querySelectorAll('.barcodeRegexResult').forEach(el => el.classList.add('d-none'));
        if (!inputValue.trim()) {
            document.getElementById('barcodeRegexEmpty').classList.remove('d-none');
            return;
        }
        let regexElement = document.getElementById('barcodeRegex').textContent;
        let regexParts = regexElement.match(/^\/(?<pattern>.*)\/(?<flags>[a-z]*)$/i);

        let { pattern, flags } = regexParts.groups;
        let regex = new RegExp(pattern, flags);

        let match = inputValue.match(regex);

        if (match && match.groups) {
            let { herbarium, specimenId } = match.groups;
            document.getElementById('barcodeRegexHerbarium').value = 'Herbarium acronym = ' + herbarium;
            document.getElementById('barcodeRegexNumber').value = 'numeric part = ' + specimenId;
            document.getElementById('barcodeRegexOk').classList.remove('d-none');
        } else {
            document.getElementById('barcodeRegexHerbarium').value = '';
            document.getElementById('barcodeRegexNumber').value = '';
            document.getElementById('barcodeRegexError').classList.remove('d-none');
        }

    }

    document.getElementById('barcodeRegexInput').addEventListener('keyup', function () {
        checkRegexMatch(this.value);
    });

    document.getElementById('barcodeRegexInput').addEventListener('paste', function (event) {
        setTimeout(() => checkRegexMatch(this.value), 0);
    });
}
