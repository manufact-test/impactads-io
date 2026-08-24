from pathlib import Path

path = Path('wp-content/plugins/impact-accs-homepage/assets/js/wp-bridge.js')
text = path.read_text(encoding='utf-8')

marker = "\t\tsection.id = 'iac-final-cta';\n"
patch_marker = "iac-final-cta__line"

if patch_marker not in text:
    if marker not in text:
        raise SystemExit('Final CTA section marker not found')

    insertion = """\t\tsection.id = 'iac-final-cta';\n\n\t\tvar finalHeadings = section.querySelectorAll('h1, h2, h3');\n\t\tfor (var headingIndex = 0; headingIndex < finalHeadings.length; headingIndex += 1) {\n\t\t\tvar finalHeading = finalHeadings[headingIndex];\n\t\t\tif (normalize(finalHeading.textContent) !== 'ПОЛУЧИТЕ АККАУНТ. ПРОВЕРЬТЕ. ПОТОМ ПЛАТИТЕ.') continue;\n\n\t\t\twhile (finalHeading.firstChild) finalHeading.removeChild(finalHeading.firstChild);\n\t\t\t['ПОЛУЧИТЕ АККАУНТ.', 'ПРОВЕРЬТЕ.', 'ПОТОМ ПЛАТИТЕ.'].forEach(function (line) {\n\t\t\t\tvar lineElement = document.createElement('span');\n\t\t\t\tlineElement.className = 'iac-final-cta__line';\n\t\t\t\tlineElement.style.display = 'block';\n\t\t\t\tlineElement.textContent = line;\n\t\t\t\tfinalHeading.appendChild(lineElement);\n\t\t\t});\n\t\t\tbreak;\n\t\t}\n"""
    text = text.replace(marker, insertion, 1)

if "['ПОЛУЧИТЕ АККАУНТ.', 'ПРОВЕРЬТЕ.', 'ПОТОМ ПЛАТИТЕ.']" not in text:
    raise SystemExit('Three-line heading patch was not applied')

path.write_text(text, encoding='utf-8')

for technical in (
    Path('.github/workflows/oneoff-final-heading-lines.yml'),
    Path('.github/oneoff_final_heading_lines.py'),
):
    if technical.exists():
        technical.unlink()
