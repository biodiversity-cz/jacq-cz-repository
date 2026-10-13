```bash
python3.14 -m venv .venv && source .venv/bin/activate
python3 -m pip install xsd-validator

python3 -m xsd_validator ccmm_merged.xsd test.xml
```
