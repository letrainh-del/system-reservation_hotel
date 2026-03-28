const dashboardPage = document.querySelector("#dashboardPage");
const reservationsPage = document.querySelector("#reservationsPage");
const navLinks = document.querySelectorAll(".nav a");

// Hide all pages except dashboard initially
reservationsPage.style.display = "none";

navLinks.forEach((link) => {
  link.addEventListener("click", () => {
    const page = link.getAttribute("data-page");

    // Remove active class
    navLinks.forEach((l) => l.classList.remove("active"));
    link.classList.add("active");

    if (page === "reservations") {
      dashboardPage.style.display = "none";
      reservationsPage.style.display = "block";
    } else {
      dashboardPage.style.display = "block";
      reservationsPage.style.display = "none";
    }
  });
});

/* ---------------- ApexCharts Setup ---------------- */
const apexTheme = {
  theme: { mode: "dark" },
  chart: {
    foreColor: "#cfcfcf",
    toolbar: { show: false },
    animations: { speed: 600 },
  },
  grid: { borderColor: "rgba(255,255,255,0.08)" },
  colors: ["#5DBB63", "#D4AF37", "#8884d8"],
  stroke: { curve: "smooth", width: 3 },
  tooltip: { theme: "dark" },
};

/* ---------------- Column Bookings Chart ---------------- */
const bookingsOptions = {
  ...apexTheme,
  series: [
    {
      name: "Bookings",
      data: [120, 150, 110, 80, 210, 160, 280], // YOUR VALUES
    },
  ],
  chart: {
    type: "bar",
    height: 300,
    background: "transparent", // ✅ transparent background
  },
  plotOptions: {
    bar: {
      columnWidth: "45%",
      borderRadius: 10,
      dataLabels: {
        position: "top", // value goes above bars
      },
    },
  },
  dataLabels: {
    enabled: true,
    offsetY: -20,
    style: {
      fontSize: "14px",
      fontWeight: "600",
      colors: ["#D4AF37"], // gold labels
    },
  },
  colors: ["#5DBB63"], // main bar color
  xaxis: {
    categories: ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"],
    labels: { style: { colors: "#ccc" } },
  },
  yaxis: {
    labels: { style: { colors: "#777" } },
  },
  grid: { borderColor: "#ffffff14" },
  tooltip: { theme: "dark" },
};

new ApexCharts(
  document.querySelector("#chartBookings"),
  bookingsOptions
).render();

/* ---------- Updated Room Status Donut Chart ---------- */
const occupancyOptions = {
  ...apexTheme,
  chart: {
    type: "donut",
    height: 250,
    background: "transparent", // ✅ transparent background
  },
  series: [68, 22, 10, 5], // Updated data for 4 statuses
  labels: ["Occupied", "Available", "Check-out", "Maintenance"],
  colors: ["#7FDBA5", "#E9D27C", "#A4B9FF", "#FF9F7B"], // Updated colors

  plotOptions: {
    pie: {
      donut: {
        size: "55%",
        labels: {
          show: true,
          total: {
            show: true,
            label: "Rooms",
            formatter: () => 200,
          },
          value: {
            formatter: (val) => val + "%",
          },
        },
      },
    },
  },

  dataLabels: { enabled: false }, // ✅ remove slice borders & labels on slices
  stroke: {
    width: 12,
    colors: ["transparent"], // ✅ make donut stroke invisible
  },

  legend: {
    show: true,
    position: "bottom",
    labels: { colors: "#ccc" },
    size: 14,
  },
};

new ApexCharts(
  document.querySelector("#chartOccupancy"),
  occupancyOptions
).render();

/* ---------------- FullCalendar ---------------- */
document.addEventListener("DOMContentLoaded", function () {
  const calendarEl = document.getElementById("calendar");
  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: "dayGridMonth",
    height: "auto",
    headerToolbar: {
      left: "prev,next today",
      center: "title",
      right: "dayGridMonth,timeGridWeek,listWeek",
    },
    events: [
      { title: "Room 508 — Ali M.", start: "2025-11-06", end: "2025-11-09" },
      {
        title: "Room 1203 — Fatima N.",
        start: "2025-11-08",
        end: "2025-11-12",
      },
      { title: "Maintenance: Pool", start: "2025-11-10" },
      { title: "Group (12 rooms)", start: "2025-11-14", end: "2025-11-17" },
    ],
  });
  calendar.render();
});
// Highlight days that have events
function highlightBookedDays() {
  document.querySelectorAll(".fc-event").forEach((ev) => {
    const dayCell = ev.closest(".fc-daygrid-day");
    if (dayCell) {
      dayCell.style.background = "rgba(93, 187, 99, 0.07)"; /* soft highlight */
      dayCell.style.cursor = "pointer";
      dayCell.style.borderRadius = "4px";
    }
  });
}

calendar.render();
setTimeout(highlightBookedDays, 200);
/* ---------------- Navigation ---------------- */
