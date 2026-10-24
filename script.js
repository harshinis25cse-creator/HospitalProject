
document.addEventListener("DOMContentLoaded", function () {
    const menuLinks = document.querySelectorAll(".menu-link");
    const pages = document.querySelectorAll(".page");
    const pageTitle = document.getElementById("pageTitle");

    const patientForm = document.getElementById("patientForm");
    const message = document.getElementById("message");

    let patients = [];

    // -------------------------------
    // 1. PAGE NAVIGATION
    // -------------------------------
    function showPage(pageId) {
        pages.forEach(function (page) {
            page.classList.toggle("active-page", page.id === pageId);
        });

        menuLinks.forEach(function (link) {
            link.classList.toggle("active", link.dataset.page === pageId);
        });

        const activeLink = document.querySelector(
            '.menu-link[data-page="' + pageId + '"]'
        );

        if (pageTitle) {
            pageTitle.textContent = activeLink
                ? activeLink.textContent.trim()
                : "Hospital Resource Allocation";
        }

        if (pageId === "reports") {
            loadHospitalReports();
        }
    }

    menuLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            event.preventDefault();
            showPage(link.dataset.page);
        });
    });

    const goToRegister = document.getElementById("goToRegister");

    if (goToRegister) {
        goToRegister.addEventListener("click", function () {
            showPage("register");
        });
    }

    // -------------------------------
    // 2. HELPER FUNCTIONS
    // -------------------------------
    function escapeHTML(value) {
        return String(value ?? "").replace(/[&<>"']/g, function (char) {
            return {
                "&": "&amp;",
                "<": "&lt;",
                ">": "&gt;",
                '"': "&quot;",
                "'": "&#39;"
            }[char];
        });
    }

    function patientId(patient) {
        return patient.id ?? patient.patient_id ?? "";
    }

    function bedNumber(patient) {
        return patient.bed_number ?? patient.bedNumber ?? "";
    }

    function healthIssue(patient) {
        return patient.health_issue ?? patient.healthIssue ?? "";
    }

    function statusOf(patient) {
        return String(patient.status ?? "").trim().toLowerCase();
    }

    function isAdmitted(patient) {
        return ["allocated", "admitted", "1"].includes(statusOf(patient));
    }

    function isWaiting(patient) {
        return statusOf(patient) === "waiting";
    }

    function isDischarged(patient) {
        return statusOf(patient) === "discharged";
    }

    function filterPatients(status) {
        if (status === "All") {
            return patients;
        }

        if (status === "Allocated") {
            return patients.filter(isAdmitted);
        }

        if (status === "Waiting") {
            return patients.filter(isWaiting);
        }

        if (status === "Discharged") {
            return patients.filter(isDischarged);
        }

        return [];
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) element.textContent = value;
    }

    // -------------------------------
    // 3. LOAD DATABASE PATIENTS
    // -------------------------------
    async function loadPatientsFromDatabase() {
        try {
            const response = await fetch("get_patients.php", {
                cache: "no-store"
            });

            const text = await response.text();

            if (!response.ok) {
                throw new Error(text || "HTTP " + response.status);
            }

            let data;

            try {
                data = JSON.parse(text);
            } catch {
                throw new Error(
                    "get_patients.php JSON error: " + text.substring(0, 150)
                );
            }

            if (Array.isArray(data)) {
                patients = data;
            } else if (Array.isArray(data.patients)) {
                patients = data.patients;
            } else {
                throw new Error(data.message || "Invalid patient data.");
            }

            updateDashboard();
            renderPatientRecords();
            renderBedManagement();
            renderHistory();
            loadHospitalReports();

        } catch (error) {
            console.error("Loading error:", error);

            if (message) {
                message.textContent =
                    "Unable to load patients. Check get_patients.php.";
            }
        }
    }

    // -------------------------------
    // 4. DASHBOARD
    // -------------------------------
    function updateDashboard() {
        const admitted = patients.filter(isAdmitted).length;
        const waiting = patients.filter(isWaiting).length;
        const discharged = patients.filter(isDischarged).length;

        const occupiedBeds = new Set(
            patients
                .filter(isAdmitted)
                .map(function (patient) {
                    return Number(bedNumber(patient));
                })
                .filter(function (number) {
                    return number > 0;
                })
        ).size;

        setText("totalPatients", patients.length);
        setText("totalBeds", 13);
        setText("occupiedBeds", occupiedBeds);
        setText("availableBeds", 13 - occupiedBeds);
        setText("waitingPatients", waiting);
        setText("admittedPatients", admitted);
        setText("dischargedPatients", discharged);
    }

    // -------------------------------
    // 5. PATIENT RECORDS
    // -------------------------------
    function renderPatientRecords() {
        const tbody = document.getElementById("patientTable");
        if (!tbody) return;

        if (patients.length === 0) {
            tbody.innerHTML =
                '<tr><td colspan="11">No patient records found.</td></tr>';
            return;
        }

        tbody.innerHTML = patients.map(function (patient) {
            const admitted = isAdmitted(patient);

            return `
                <tr>
                    <td>${escapeHTML(patientId(patient))}</td>
                    <td>${escapeHTML(patient.name)}</td>
                    <td>${escapeHTML(patient.age)}</td>
                    <td>${escapeHTML(healthIssue(patient))}</td>
                    <td>${escapeHTML(patient.priority)}</td>
                    <td>${escapeHTML(bedNumber(patient) || "-")}</td>
                    <td>${escapeHTML(patient.status)}</td>
                    <td>${escapeHTML(patient.admission_date || "-")}</td>
                    <td>${escapeHTML(patient.discharge_date || "-")}</td>
                    <td>${escapeHTML(patient.days_stayed ?? "-")}</td>
                    <td>
                        ${admitted ? `
                            <button type="button"
                                class="discharge-btn"
                                data-id="${escapeHTML(patientId(patient))}">
                                Discharge
                            </button>
                        ` : "-"}
                    </td>
                </tr>
            `;
        }).join("");
    }

    // -------------------------------
    // 6. REGISTER PATIENT
    // -------------------------------
    if (patientForm) {
        patientForm.addEventListener("submit", async function (event) {
            event.preventDefault();

            if (message) message.textContent = "Saving patient...";

            try {
                const response = await fetch("save_patient.php", {
                    method: "POST",
                    body: new FormData(patientForm)
                });

                const result = await response.text();

                if (!response.ok ||
                    /save failed|error:|warning:|fatal error/i.test(result)) {
                    throw new Error(result || "Unable to save patient.");
                }

                if (message) {
                    message.textContent = result;
                } else {
                    alert(result);
                }

                patientForm.reset();
                await loadPatientsFromDatabase();

            } catch (error) {
                console.error("Registration error:", error);

                if (message) {
                    message.textContent = "Save failed: " + error.message;
                } else {
                    alert("Save failed: " + error.message);
                }
            }
        });
    }

    
    // -------------------------------
    // 7. DISCHARGE PATIENT
    // -------------------------------
    document.addEventListener("click", async function (event) {
        const button = event.target.closest(".discharge-btn");
        if (!button) return;

        const id = button.dataset.id;

        if (!id) {
            alert("Patient ID missing. Please refresh the page.");
            return;
        }

        if (!confirm("Discharge patient ID " + id + "?")) return;

        button.disabled = true;

        try {
            const formData = new FormData();
            formData.append("patientId", id);

            const response = await fetch("discharge_patient.php", {
                method: "POST",
                body: formData,
                cache: "no-store"
            });

            const responseText = await response.text();

            let result;

            try {
                result = JSON.parse(responseText);
            } catch {
                throw new Error(
                    "PHP JSON response error: " +
                    responseText.substring(0, 300)
                );
            }

            if (!response.ok || !result.success) {
                throw new Error(
                    result.message || "Discharge failed."
                );
            }

            alert(result.message || "Patient discharged successfully.");

            // Reload database records after successful discharge.
            await loadPatientsFromDatabase();

        } catch (error) {
            console.error("Discharge error:", error);
            alert("Discharge failed: " + error.message);
        } finally {
            button.disabled = false;
        }
    });

    // -------------------------------
    // 8. BED MANAGEMENT
    // -------------------------------
    function renderBedManagement() {
        const bedList = document.getElementById("bedList");
        if (!bedList) return;

        const beds = [];

        for (let i = 1; i <= 10; i++) {
            beds.push({ number: i, type: "Normal" });
        }

        for (let i = 101; i <= 103; i++) {
            beds.push({ number: i, type: "Buffer" });
        }

        bedList.innerHTML = beds.map(function (bed) {
            const patient = patients.find(function (item) {
                return isAdmitted(item) &&
                    Number(bedNumber(item)) === bed.number;
            });

            return `
                <button type="button"
                    class="bed-card ${patient ? "occupied" : "available"}"
                    data-bed-number="${bed.number}">
                    <strong>Bed ${bed.number}</strong>
                    <span>${bed.type}</span>
                    <span>${patient ? "Occupied" : "Available"}</span>
                </button>
            `;
        }).join("");

        bedList.querySelectorAll(".bed-card").forEach(function (card) {
            card.addEventListener("click", function () {
                showBedDetails(Number(card.dataset.bedNumber));
            });
        });
    }

    function showBedDetails(number) {
        const patient = patients.find(function (item) {
            return isAdmitted(item) &&
                Number(bedNumber(item)) === number;
        });

        const details = document.getElementById("selectedBedDetails");

        setText("selectedBedTitle", "Bed " + number + " Details");

        if (details) {
            details.innerHTML = patient
                ? `
                    <p><strong>Patient ID:</strong> ${escapeHTML(patientId(patient))}</p>
                    <p><strong>Name:</strong> ${escapeHTML(patient.name)}</p>
                    <p><strong>Age:</strong> ${escapeHTML(patient.age)}</p>
                    <p><strong>Health Issue:</strong> ${escapeHTML(healthIssue(patient))}</p>
                    <p><strong>Priority:</strong> ${escapeHTML(patient.priority)}</p>
                    <p><strong>Status:</strong> ${escapeHTML(patient.status)}</p>
                    <p><strong>Admission Date:</strong> ${escapeHTML(patient.admission_date || "-")}</p>
                `
                : "<p>This bed is currently available.</p>";
        }

        showPage("bedDetailsPage");
    }

    const backToBeds = document.getElementById("backToBeds");

    if (backToBeds) {
        backToBeds.addEventListener("click", function () {
            showPage("beds");
        });
    }

    // -------------------------------
    // 9. ALLOCATION HISTORY
    // -------------------------------
    function renderHistory() {
        const historyList = document.getElementById("historyList");
        if (!historyList) return;

        if (patients.length === 0) {
            historyList.innerHTML = "<li>No allocation history available.</li>";
            return;
        }

        historyList.innerHTML = patients.map(function (patient) {
            return `
                <li>
                    Patient ID: ${escapeHTML(patientId(patient))} |
                    Name: ${escapeHTML(patient.name)} |
                    Bed: ${escapeHTML(bedNumber(patient) || "-")} |
                    Status: ${escapeHTML(patient.status)}
                </li>
            `;
        }).join("");
    }

    // -------------------------------
    // 10. REPORT COUNTS
    // -------------------------------
    function loadHospitalReports() {
        setText("reportTotal", patients.length);
        setText("reportAdmitted", patients.filter(isAdmitted).length);
        setText("reportWaiting", patients.filter(isWaiting).length);
        setText("reportDischarged", patients.filter(isDischarged).length);

        renderHealthIssueReport();
    }

    // -------------------------------
    // 11. REPORT CARD CLICK
    // -------------------------------
    function openReport(status) {
        const details = document.getElementById("reportPatientDetails");
        const tbody = document.getElementById("reportPatientTable");

        const headingTitles = {
            All: "All Registered Patients",
            Allocated: "Currently Admitted Patients",
            Waiting: "Waiting Patients",
            Discharged: "Discharged Patients"
        };

        setText("reportTableTitle", headingTitles[status] || "Patient Details");

        const filtered = filterPatients(status);

        if (tbody) {
            if (filtered.length === 0) {
                tbody.innerHTML =
                    '<tr><td colspan="9">No patients found for this report.</td></tr>';
            } else {
                tbody.innerHTML = filtered.map(function (patient) {
                    return `
                        <tr>
                            <td>${escapeHTML(patientId(patient))}</td>
                            <td>${escapeHTML(patient.name)}</td>
                            <td>${escapeHTML(patient.age)}</td>
                            <td>${escapeHTML(healthIssue(patient))}</td>
                            <td>${escapeHTML(patient.priority)}</td>
                            <td>${escapeHTML(bedNumber(patient) || "-")}</td>
                            <td>${escapeHTML(patient.status)}</td>
                            <td>${escapeHTML(patient.admission_date || "-")}</td>
                            <td>${escapeHTML(patient.discharge_date || "-")}</td>
                        </tr>
                    `;
                }).join("");
            }
        }

        if (details) {
            details.style.display = "block";
            details.scrollIntoView({
                behavior: "smooth",
                block: "start"
            });
        }
    }

    document.querySelectorAll(".report-clickable").forEach(function (card) {
        card.style.cursor = "pointer";

        card.addEventListener("click", function () {
            openReport(card.dataset.reportStatus || "All");
        });

        card.addEventListener("keydown", function (event) {
            if (event.key === "Enter" || event.key === " ") {
                event.preventDefault();
                openReport(card.dataset.reportStatus || "All");
            }
        });
    });

    // -------------------------------
    // 12. HEALTH ISSUE-WISE REPORT
    // -------------------------------
    function renderHealthIssueReport() {
        const tbody = document.getElementById("healthReportTable");
        if (!tbody) return;

        const counts = {};

        patients.forEach(function (patient) {
            const issue = healthIssue(patient) || "Not specified";
            counts[issue] = (counts[issue] || 0) + 1;
        });

        const entries = Object.entries(counts);

        tbody.innerHTML = entries.length
            ? entries.map(function ([issue, count]) {
                return `
                    <tr>
                        <td>${escapeHTML(issue)}</td>
                        <td>${count}</td>
                    </tr>
                `;
            }).join("")
            : '<tr><td colspan="2">No patient records found.</td></tr>';
    }

    // -------------------------------
    // 13. DATE-WISE REPORT
    // -------------------------------
    const dailyReportBtn = document.getElementById("dailyReportBtn");

    if (dailyReportBtn) {
        dailyReportBtn.addEventListener("click", function () {
            const dateInput = document.getElementById("reportDate");
            const result = document.getElementById("dailyReportResult");

            if (!dateInput || !dateInput.value) {
                alert("Please select a date.");
                return;
            }

            const selectedDate = dateInput.value;

            const dailyPatients = patients.filter(function (patient) {
                return String(patient.admission_date || "").substring(0, 10)
                    === selectedDate;
            });

            if (result) {
                if (dailyPatients.length === 0) {
                    result.innerHTML =
                        "<p>No patients registered on " +
                        escapeHTML(selectedDate) + ".</p>";
                } else {
                    result.innerHTML = `
                        <h3>Patients registered on ${escapeHTML(selectedDate)}</h3>
                        <p>Total: ${dailyPatients.length}</p>
                        <ul>
                            ${dailyPatients.map(function (patient) {
                                return `
                                    <li>
                                        ID: ${escapeHTML(patientId(patient))},
                                        Name: ${escapeHTML(patient.name)},
                                        Status: ${escapeHTML(patient.status)}
                                    </li>
                                `;
                            }).join("")}
                        </ul>
                    `;
                }
            }
        });
    }

    // -------------------------------
    // 14. REFRESH REPORTS
    // -------------------------------
    const refreshReports = document.getElementById("refreshReports");

    if (refreshReports) {
        refreshReports.addEventListener("click", async function () {
            await loadPatientsFromDatabase();
            alert("Reports refreshed.");
        });
    }

    // -------------------------------
    // 15. INITIAL LOAD
    // -------------------------------
    loadPatientsFromDatabase();
});
