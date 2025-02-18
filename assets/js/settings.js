export default function regexValidation() {

    document.getElementById('barcodeRegexInput').addEventListener('keyup', function () {
        checkBarcodeRegexMatch(this.value);
    });

    document.getElementById('barcodeRegexInput').addEventListener('paste', function (event) {
        setTimeout(() => checkBarcodeRegexMatch(this.value), 0);
    });

    document.getElementById('filenameRegexInput').addEventListener('keyup', function () {
        checkFilenameRegexMatch(this.value);
    });

    document.getElementById('filenameRegexInput').addEventListener('paste', function (event) {
        setTimeout(() => checkFilenameRegexMatch(this.value), 0);
    });
}

function checkBarcodeRegexMatch(inputValue) {
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
        document.getElementById('barcodeRegexNumber').value = 'ID = ' + specimenId;
        document.getElementById('barcodeRegexOk').classList.remove('d-none');
    } else {
        document.getElementById('barcodeRegexHerbarium').value = '';
        document.getElementById('barcodeRegexNumber').value = '';
        document.getElementById('barcodeRegexError').classList.remove('d-none');
    }

}

function checkFilenameRegexMatch(inputValue) {
    document.querySelectorAll('.filenameRegexResult').forEach(el => el.classList.add('d-none'));
    if (!inputValue.trim()) {
        document.getElementById('filenameRegexEmpty').classList.remove('d-none');
        return;
    }
    let regexElement = document.getElementById('filenameRegex').textContent;
    let regexParts = regexElement.match(/^\/(?<pattern>.*)\/(?<flags>[a-z]*)$/i);

    let { pattern, flags } = regexParts.groups;
    let regex = new RegExp(pattern, flags);

    let match = inputValue.match(regex);

    if (match && match.groups) {
        let { herbarium, specimenId } = match.groups;
        document.getElementById('filenameRegexHerbarium').value = 'Herbarium acronym = ' + herbarium;
        document.getElementById('filenameRegexNumber').value = 'ID = ' + specimenId;
        document.getElementById('filenameRegexOk').classList.remove('d-none');
    } else {
        document.getElementById('filenameRegexHerbarium').value = '';
        document.getElementById('filenameRegexNumber').value = '';
        document.getElementById('filenameRegexError').classList.remove('d-none');
    }

}
