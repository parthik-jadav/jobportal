# JobPortal Developer Screenshots Catalog

This directory contains high-fidelity full-page screenshots of all 27 application views across Public, Student, Employer, and Administrator portals, adhering to the "Aura Talent UI" design system.

## Browsing the Gallery
To view all screenshots interactively with lightbox zoom:
- Open [`screenshots/index.html`](index.html) in any web browser, or navigate to `http://localhost/jobportal/screenshots/index.html`.

## Regnerating Screenshots
Run the automated Playwright capture script:
```bash
python generate_dev_screenshots.py
```

## Running End-to-End Tests
Verify all 36 automated Playwright E2E tests:
```bash
python test_e2e.py
```
