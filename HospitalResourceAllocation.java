import java.util.*;

// ================= PATIENT CLASS =================
class Patient {

    protected int id;
    protected String name;
    protected int age;
    protected String healthIssue;
    protected int priority;

    public Patient(int id, String name, int age, String healthIssue) {

        this.id = id;
        this.name = name;
        this.age = age;
        this.healthIssue = healthIssue;

        this.priority = calculatePriority(healthIssue);
    }

    public int getId() {
        return id;
    }

    public String getName() {
        return name;
    }

    public int getAge() {
        return age;
    }

    public String getHealthIssue() {
        return healthIssue;
    }

    public int getPriority() {
        return priority;
    }

    // ================= PRIORITY CALCULATION =================
    public int calculatePriority(String issue) {

        issue = issue.toLowerCase();

        // Priority 5 - Critical / Emergency
        if (issue.contains("heart attack") ||
            issue.contains("cardiac arrest") ||
            issue.contains("stroke") ||
            issue.contains("severe bleeding") ||
            issue.contains("breathing difficulty") ||
            issue.contains("unconscious") ||
            issue.contains("severe accident") ||
            issue.contains("severe injury")) {

            return 5;
        }

        // Priority 4 - High
        if (issue.contains("major injury") ||
            issue.contains("severe infection") ||
            issue.contains("snake bite") ||
            issue.contains("burn")) {

            return 4;
        }

        // Priority 3 - Medium
        if (issue.contains("fracture") ||
            issue.contains("high fever") ||
            issue.contains("pneumonia") ||
            issue.contains("asthma")) {

            return 3;
        }

        // Priority 2 - Low Medium
        if (issue.contains("infection") ||
            issue.contains("diabetes") ||
            issue.contains("vomiting") ||
            issue.contains("stomach pain") ||
            issue.contains("minor accident") ||
            issue.contains("small wound") ||
            issue.contains("less wound")) {

            return 2;
        }

        // Priority 1 - Low
        return 1;
    }

    public String getPriorityLevel() {

        switch (priority) {

            case 5:
                return "CRITICAL / EMERGENCY";

            case 4:
                return "HIGH";

            case 3:
                return "MEDIUM";

            case 2:
                return "LOW-MEDIUM";

            default:
                return "LOW";
        }
    }

    // ================= DISPLAY PATIENT =================
    public void displayPatient() {

        System.out.println("----------------------------------");
        System.out.println("Patient ID      : " + id);
        System.out.println("Patient Name    : " + name);
        System.out.println("Patient Age     : " + age);
        System.out.println("Health Issue    : " + healthIssue);
        System.out.println("Priority Level  : " + getPriorityLevel());
    }
}


// ================= NORMAL PATIENT =================
class NormalPatient extends Patient {

    public NormalPatient(int id, String name, int age, String healthIssue) {

        super(id, name, age, healthIssue);
    }
}


// ================= PRIORITY PATIENT =================
class PriorityPatient extends Patient {

    public PriorityPatient(int id, String name, int age, String healthIssue) {

        super(id, name, age, healthIssue);
    }
}


// ================= EMERGENCY PATIENT =================
class EmergencyPatient extends Patient {

    public EmergencyPatient(int id, String name, int age, String healthIssue) {

        super(id, name, age, healthIssue);
    }
}


// ================= BED CLASS =================
class Bed {

    private int bedNumber;
    private boolean occupied;
    private Patient patient;

    public Bed(int bedNumber) {

        this.bedNumber = bedNumber;
        this.occupied = false;
        this.patient = null;
    }

    public int getBedNumber() {
        return bedNumber;
    }

    public boolean isOccupied() {
        return occupied;
    }

    public Patient getPatient() {
        return patient;
    }

    public void allocate(Patient patient) {

        this.patient = patient;
        this.occupied = true;
    }

    public void release() {

        this.patient = null;
        this.occupied = false;
    }

    public void displayBed() {

        if (occupied) {

            System.out.println(
                "Bed " + bedNumber +
                " -> OCCUPIED by " +
                patient.getName() +
                " (Age: " +
                patient.getAge() + ")"
            );

        } else {

            System.out.println(
                "Bed " + bedNumber +
                " -> AVAILABLE"
            );
        }
    }
}


// ================= BED ARRAY =================
class BedArray {

    private Bed[] beds;

    public BedArray(int size) {

        beds = new Bed[size];

        for (int i = 0; i < size; i++) {

            beds[i] = new Bed(i + 1);
        }
    }

    public Bed findAvailableBed() {

        for (Bed bed : beds) {

            if (!bed.isOccupied()) {

                return bed;
            }
        }

        return null;
    }

    public void displayBeds() {

        System.out.println(
            "\n========== NORMAL BED STATUS =========="
        );

        for (Bed bed : beds) {

            bed.displayBed();
        }
    }
}


// ================= BUFFER ROOM =================
class BufferRoom {

    private Bed[] bufferBeds;

    public BufferRoom(int size) {

        bufferBeds = new Bed[size];

        for (int i = 0; i < size; i++) {

            bufferBeds[i] = new Bed(101 + i);
        }
    }

    public Bed findAvailableBed() {

        for (Bed bed : bufferBeds) {

            if (!bed.isOccupied()) {

                return bed;
            }
        }

        return null;
    }

    public void displayBufferBeds() {

        System.out.println(
            "\n========== BUFFER ROOM STATUS =========="
        );

        for (Bed bed : bufferBeds) {

            bed.displayBed();
        }
    }
}


// ================= LINKED LIST =================
class ResourceNode {

    Patient patient;
    ResourceNode next;

    public ResourceNode(Patient patient) {

        this.patient = patient;
        this.next = null;
    }
}


class ResourceLinkedList {

    private ResourceNode head;

    public void add(Patient patient) {

        ResourceNode newNode =
            new ResourceNode(patient);

        if (head == null) {

            head = newNode;
            return;
        }

        ResourceNode current = head;

        while (current.next != null) {

            current = current.next;
        }

        current.next = newNode;
    }

    public void display() {

        System.out.println(
            "\n========== RESOURCE LINKED LIST =========="
        );

        if (head == null) {

            System.out.println(
                "No resource records."
            );

            return;
        }

        ResourceNode current = head;

        while (current != null) {

            System.out.println(
                "Patient: " +
                current.patient.getName() +
                " | Age: " +
                current.patient.getAge() +
                " | Issue: " +
                current.patient.getHealthIssue()
            );

            current = current.next;
        }
    }
}


// ================= STACK =================
class AllocationStack {

    private Stack<String> stack;
    private int maxSize;

    public AllocationStack(int maxSize) {

        this.maxSize = maxSize;
        stack = new Stack<>();
    }

    public void push(String record) {

        if (stack.size() < maxSize) {

            stack.push(record);

        } else {

            System.out.println(
                "Allocation history stack is full."
            );
        }
    }

    public void display() {

        System.out.println(
            "\n========== ALLOCATION HISTORY =========="
        );

        if (stack.isEmpty()) {

            System.out.println(
                "No allocation history."
            );

            return;
        }

        System.out.println("TOP");

        for (int i = stack.size() - 1; i >= 0; i--) {

            System.out.println(
                stack.get(i)
            );
        }
    }
}


// ================= MAIN CLASS =================
public class HospitalResourceAllocation {

    public static void main(String[] args) {

        Scanner sc = new Scanner(System.in);

        // ================= FIXED SIZES =================

        final int NORMAL_BEDS = 10;
        final int BUFFER_BEDS = 3;

        // Stack is for HISTORY,
        // so its size does not need to equal bed size.
        final int STACK_SIZE = 20;


        System.out.println(
            "\n=================================================="
        );

        System.out.println(
            "       HOSPITAL RESOURCE ALLOCATION SYSTEM"
        );

        System.out.println(
            "=================================================="
        );


        // ================= NORMAL BEDS =================

        System.out.println(
            "\n================ ARRAY INPUT ================"
        );

        System.out.println(
            "Fixed Normal Beds: " + NORMAL_BEDS
        );

        BedArray normalBeds =
            new BedArray(NORMAL_BEDS);


        // ================= BUFFER ROOM =================

        System.out.println(
            "\n============= BUFFER ROOM ============="
        );

        System.out.println(
            "Fixed Buffer Beds: " + BUFFER_BEDS
        );

        BufferRoom bufferRoom =
            new BufferRoom(BUFFER_BEDS);


        // ================= STACK =================

        System.out.println(
            "\n============= STACK ============="
        );

        System.out.println(
            "Fixed Stack Size: " + STACK_SIZE
        );

        AllocationStack allocationStack =
            new AllocationStack(STACK_SIZE);


        // ================= LINKED LIST =================

        ResourceLinkedList resourceList =
            new ResourceLinkedList();


        // ================= PRIORITY QUEUE =================

        PriorityQueue<Patient> priorityQueue =
            new PriorityQueue<>(
                (p1, p2) -> {

                    if (p1.getPriority() !=
                        p2.getPriority()) {

                        return Integer.compare(
                            p2.getPriority(),
                            p1.getPriority()
                        );
                    }

                    return Integer.compare(
                        p1.getId(),
                        p2.getId()
                    );
                }
            );


        // ================= PATIENT INPUT =================

        System.out.print(
            "\nEnter number of priority queue patients: "
        );

        int numberOfPatients = sc.nextInt();
        sc.nextLine();


        for (int i = 0;
             i < numberOfPatients;
             i++) {

            System.out.println(
                "\n========== PATIENT " +
                (i + 1) +
                " =========="
            );


            System.out.print(
                "Enter patient ID: "
            );

            int id = sc.nextInt();
            sc.nextLine();


            System.out.print(
                "Enter patient name: "
            );

            String name = sc.nextLine();


            System.out.print(
                "Enter patient age: "
            );

            int age = sc.nextInt();
            sc.nextLine();


            System.out.print(
                "Enter health issue: "
            );

            String issue = sc.nextLine();


            // ================= CREATE PATIENT =================

            Patient patient =
                new PriorityPatient(
                    id,
                    name,
                    age,
                    issue
                );


            // ================= EMERGENCY DETECTION =================

            if (patient.getPriority() == 5) {

                patient =
                    new EmergencyPatient(
                        id,
                        name,
                        age,
                        issue
                    );

                System.out.println(
                    "\nPatient Type : EMERGENCY"
                );

            } else {

                System.out.println(
                    "\nPatient Type : PRIORITY"
                );
            }


            System.out.println(
                "Patient Health Issue : " +
                patient.getHealthIssue()
            );

            System.out.println(
                "Patient Age          : " +
                patient.getAge()
            );

            // Assigned Priority REMOVED

            System.out.println(
                "Priority Level       : " +
                patient.getPriorityLevel()
            );


            // ================= ADD TO QUEUE =================

            priorityQueue.add(patient);


            // ================= ADD TO LINKED LIST =================

            resourceList.add(patient);
        }


        // ================= PRIORITY QUEUE DISPLAY =================

        System.out.println(
            "\n\n========== PRIORITY QUEUE ORDER =========="
        );

        PriorityQueue<Patient> tempQueue =
            new PriorityQueue<>(priorityQueue);


        while (!tempQueue.isEmpty()) {

            Patient p = tempQueue.poll();

            System.out.println(
                p.getName() +
                " | Age: " +
                p.getAge() +
                " | " +
                p.getPriorityLevel()
            );
        }


        // ================= BED ALLOCATION =================

        System.out.println(
            "\n\n========== BED ALLOCATION =========="
        );


        while (!priorityQueue.isEmpty()) {

            Patient patient =
                priorityQueue.poll();


            Bed allocatedBed =
                normalBeds.findAvailableBed();


            // ================= NORMAL BED AVAILABLE =================

            if (allocatedBed != null) {

                allocatedBed.allocate(patient);


                String record =
                    patient.getName() +
                    " (Age " +
                    patient.getAge() +
                    ") -> Bed " +
                    allocatedBed.getBedNumber();


                // Add allocation history to stack
                allocationStack.push(record);


                System.out.println(
                    patient.getName() +
                    " allocated to Bed " +
                    allocatedBed.getBedNumber()
                );
            }


            // ================= NORMAL BEDS FULL =================

            else {

                // Only Emergency patients
                // can use Buffer Room

                if (patient.getPriority() == 5) {

                    allocatedBed =
                        bufferRoom.findAvailableBed();


                    // Buffer bed available
                    if (allocatedBed != null) {

                        allocatedBed.allocate(patient);


                        String record =
                            patient.getName() +
                            " (Age " +
                            patient.getAge() +
                            ") -> Buffer Bed " +
                            allocatedBed.getBedNumber();


                        allocationStack.push(record);


                        System.out.println(
                            "EMERGENCY: " +
                            patient.getName() +
                            " allocated to Buffer Bed " +
                            allocatedBed.getBedNumber()
                        );
                    }


                    // Buffer also full
                    else {

                        System.out.println(
                            patient.getName() +
                            " is WAITING - " +
                            "No bed available."
                        );
                    }

                }


                // ================= NORMAL PATIENT =================

                else {

                    System.out.println(
                        patient.getName() +
                        " is WAITING - " +
                        "No normal bed available."
                    );
                }
            }
        }


        // ================= DISPLAY ALL =================

        normalBeds.displayBeds();

        bufferRoom.displayBufferBeds();

        resourceList.display();

        allocationStack.display();


        System.out.println(
            "\n=================================================="
        );

        System.out.println(
            "       HOSPITAL RESOURCE ALLOCATION ENDED"
        );

        System.out.println(
            "=================================================="
        );


        sc.close();
    }
}